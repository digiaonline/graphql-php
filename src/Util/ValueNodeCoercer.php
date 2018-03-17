<?php

namespace Digia\GraphQL\Util;

use Digia\GraphQL\Error\CoercingException;
use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\EnumValueNode;
use Digia\GraphQL\Language\Node\ListValueNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NullValueNode;
use Digia\GraphQL\Language\Node\ObjectFieldNode;
use Digia\GraphQL\Language\Node\ObjectValueNode;
use Digia\GraphQL\Language\Node\ValueNodeInterface;
use Digia\GraphQL\Language\Node\VariableNode;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InputTypeInterface;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\TypeInterface;

/**
 * Produces a PHP value given a GraphQL Value AST.
 *
 * A GraphQL type must be provided, which will be used to interpret different
 * GraphQL Value literals.
 *
 * Returns `undefined` when the value could not be validly coerced according to
 * the provided type.
 *
 * | GraphQL Value        | JSON Value    |
 * | -------------------- | ------------- |
 * | Input Object         | Object        |
 * | List                 | Array         |
 * | Boolean              | Boolean       |
 * | String               | String        |
 * | Int / Float          | Number        |
 * | Enum Value           | Mixed         |
 * | NullValue            | null          |
 */
class ValueNodeCoercer
{
    /**
     * @param NodeInterface|ValueNodeInterface|null   $node
     * @param InputTypeInterface|NonNullType|ListType $type
     * @param array                                   $variables
     * @return mixed|null
     * @throws InvalidTypeException
     * @throws InvariantException
     * @throws CoercingException
     */
    public function coerce(?NodeInterface $node, TypeInterface $type, array $variables = [])
    {
        if (null === $node) {
            throw new CoercingException('Node is not defined.');
        }

        if ($type instanceof NonNullType) {
            return $this->coerceNonNullType($node, $type, $variables);
        }

        if ($node instanceof NullValueNode) {
            return null;
        }

        if ($node instanceof VariableNode) {
            $variableName = $node->getNameValue();

            if (!isset($variables[$variableName])) {
                throw new CoercingException(\sprintf('Cannot coerce value for missing variable "%s".', $variableName));
            }

            // Note: we're not doing any checking that this variable is correct. We're
            // assuming that this query has been validated and the variable usage here
            // is of the correct type.
            return $variables[$variableName];
        }

        if ($type instanceof ListType) {
            return $this->coerceListType($node, $type, $variables);
        }

        if ($type instanceof InputObjectType) {
            return $this->coerceInputObjectType($node, $type, $variables);
        }

        if ($type instanceof EnumType) {
            return $this->coerceEnumType($node, $type);
        }

        if ($type instanceof ScalarType) {
            return $this->coerceScalarType($node, $type, $variables);
        }

        throw new InvalidTypeException(\sprintf('Unknown type: %s', $type));
    }

    /**
     * @param NodeInterface|ValueNodeInterface $node
     * @param NonNullType                      $type
     * @param array                            $variables
     * @return mixed|null
     * @throws InvalidTypeException
     * @throws InvariantException
     * @throws CoercingException
     */
    protected function coerceNonNullType(NodeInterface $node, NonNullType $type, array $variables = [])
    {
        if ($node instanceof NullValueNode) {
            throw new CoercingException('Cannot coerce non-null values from null value node.');
        }

        return $this->coerce($node, $type->getOfType(), $variables);
    }

    /**
     * @param NodeInterface|ValueNodeInterface $node
     * @param ListType                         $type
     * @return array|null
     * @throws InvalidTypeException
     * @throws InvariantException
     * @throws CoercingException
     */
    protected function coerceListType(NodeInterface $node, ListType $type, array $variables = [])
    {
        $itemType = $type->getOfType();

        if ($node instanceof ListValueNode) {
            $coercedValues = [];

            foreach ($node->getValues() as $value) {
                if ($this->isMissingVariable($value, $variables)) {
                    if ($itemType instanceof NonNullType) {
                        // If an array contains a missing variable, it is either coerced to
                        // null or if the item type is non-null, it considered invalid.
                        throw new CoercingException('Cannot coerce value for missing non-null list item.');
                    }

                    $coercedValues[] = null;
                } else {
                    $coercedValues[] = $this->coerce($value, $itemType, $variables);
                }
            }

            return $coercedValues;
        }

        return [$this->coerce($node, $itemType, $variables)];
    }

    /**
     * @param NodeInterface|ValueNodeInterface $node
     * @param InputObjectType                  $type
     * @param array                            $variables
     * @return array|null
     * @throws InvalidTypeException
     * @throws InvariantException
     * @throws CoercingException
     */
    protected function coerceInputObjectType(NodeInterface $node, InputObjectType $type, array $variables = [])
    {
        if (!$node instanceof ObjectValueNode) {
            throw new CoercingException('Input object values can only be coerced form object value nodes.');
        }

        $coercedValues = [];

        /** @var ObjectFieldNode[] $fieldNodes */
        $fieldNodes = keyMap($node->getFields(), function (ObjectFieldNode $value) {
            return $value->getNameValue();
        });

        foreach ($type->getFields() as $field) {
            $name      = $field->getName();
            $fieldNode = $fieldNodes[$name] ?? null;

            if (null === $fieldNode || $this->isMissingVariable($fieldNode->getValue(), $variables)) {
                if (null !== $field->getDefaultValue()) {
                    $coercedValues[$name] = $field->getDefaultValue();
                } elseif ($field->getType() instanceof NonNullType) {
                    throw new CoercingException('Cannot coerce input object value for missing non-null field.');
                }
                continue;
            }

            $fieldValue = $this->coerce($fieldNode->getValue(), $field->getType(), $variables);

            $coercedValues[$name] = $fieldValue;
        }

        return $coercedValues;
    }

    /**
     * @param NodeInterface|ValueNodeInterface $node
     * @param EnumType                         $type
     * @return mixed|null
     * @throws InvariantException
     * @throws CoercingException
     */
    protected function coerceEnumType(NodeInterface $node, EnumType $type)
    {
        if (!$node instanceof EnumValueNode) {
            throw new CoercingException('Enum values can only be coerced from enum value nodes.');
        }

        $name = $node->getValue();

        $enumValue = $type->getValue($name);

        if (null === $enumValue) {
            throw new CoercingException(\sprintf('Cannot coerce enum value for missing value "%s".', $name));
        }

        return $enumValue->getValue();
    }

    /**
     * @param NodeInterface|ValueNodeInterface $node
     * @param ScalarType                       $type
     * @param array                            $variables
     * @return mixed|null
     * @throws CoercingException
     */
    protected function coerceScalarType(NodeInterface $node, ScalarType $type, array $variables = [])
    {
        // Scalars fulfill parsing a literal value via parseLiteral().
        // Invalid values represent a failure to parse correctly, in which case
        // no value is returned.
        $result = null;

        try {
            $result = $type->parseLiteral($node, $variables);
        } catch (\Exception $e) {

        }

        if (null === $result) {
            throw new CoercingException(\sprintf('Failed to parse literal for scalar type "%s".', (string)$type));
        }

        return $result;
    }

    /**
     * @param ValueNodeInterface $node
     * @param array              $variables
     * @return bool
     */
    protected function isMissingVariable(ValueNodeInterface $node, array $variables): bool
    {
        return $node instanceof VariableNode && isset($variables[$node->getNameValue()]);
    }
}
