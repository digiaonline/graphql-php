<?php

namespace Digia\GraphQL\Util;

use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Error\ResolutionException;
use Digia\GraphQL\Language\Node\ArgumentNode;
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
use function Digia\GraphQL\printNode;

class ValueHelper
{
    /**
     * @param array $argumentsA
     * @param array $argumentsB
     * @return bool
     */
    function compareArguments(array $argumentsA, array $argumentsB): bool
    {
        if (\count($argumentsA) !== \count($argumentsB)) {
            return false;
        }

        return arrayEvery($argumentsA, function (ArgumentNode $argumentA) use ($argumentsB) {
            $argumentB = find($argumentsB, function (ArgumentNode $argument) use ($argumentA) {
                return $argument->getNameValue() === $argumentA->getNameValue();
            });

            if (null === $argumentB) {
                return false;
            }

            return $this->compareValues($argumentA->getValue(), $argumentB->getValue());
        });
    }

    /**
     * @param $valueA
     * @param $valueB
     * @return bool
     */
    public function compareValues($valueA, $valueB): bool
    {
        return printNode($valueA) === printNode($valueB);
    }

    /**
     * Produces a PHP value given a GraphQL Value AST.
     *
     * A GraphQL type must be provided, which will be used to interpret different
     * GraphQL Value literals.
     *
     * Returns `undefined` when the value could not be validly resolved according to
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
     *
     * @param NodeInterface|ValueNodeInterface|null   $node
     * @param InputTypeInterface|NonNullType|ListType $type
     * @param array                                   $variables
     * @return mixed|null
     * @throws InvalidTypeException
     * @throws InvariantException
     * @throws ResolutionException
     */
    public function fromAST(?NodeInterface $node, TypeInterface $type, array $variables = [])
    {
        if (null === $node) {
            throw new ResolutionException('Node is not defined.');
        }

        if ($type instanceof NonNullType) {
            return $this->resolveNonNullType($node, $type, $variables);
        }

        if ($node instanceof NullValueNode) {
            // This is explicitly returning the value null.
            return null;
        }

        if ($node instanceof VariableNode) {
            $variableName = $node->getNameValue();

            if (!isset($variables[$variableName])) {
                throw new ResolutionException(
                    \sprintf('Cannot resolve value for missing variable "%s".', $variableName)
                );
            }

            // Note: we're not doing any checking that this variable is correct. We're
            // assuming that this query has been validated and the variable usage here
            // is of the correct type.
            return $variables[$variableName];
        }

        if ($type instanceof ListType) {
            return $this->resolveListType($node, $type, $variables);
        }

        if ($type instanceof InputObjectType) {
            return $this->resolveInputObjectType($node, $type, $variables);
        }

        if ($type instanceof EnumType) {
            return $this->resolveEnumType($node, $type);
        }

        if ($type instanceof ScalarType) {
            return $this->resolveScalarType($node, $type, $variables);
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
     * @throws ResolutionException
     */
    protected function resolveNonNullType(NodeInterface $node, NonNullType $type, array $variables = [])
    {
        if ($node instanceof NullValueNode) {
            throw new ResolutionException('Cannot resolve non-null values from null value node.');
        }

        return $this->fromAST($node, $type->getOfType(), $variables);
    }

    /**
     * @param NodeInterface|ValueNodeInterface $node
     * @param ListType                         $type
     * @return array|null
     * @throws InvalidTypeException
     * @throws InvariantException
     * @throws ResolutionException
     */
    protected function resolveListType(NodeInterface $node, ListType $type, array $variables = [])
    {
        $itemType = $type->getOfType();

        if ($node instanceof ListValueNode) {
            $resolvedValues = [];

            foreach ($node->getValues() as $value) {
                if ($this->isMissingVariable($value, $variables)) {
                    if ($itemType instanceof NonNullType) {
                        // If an array contains a missing variable, it is either resolved to
                        // null or if the item type is non-null, it considered invalid.
                        throw new ResolutionException('Cannot resolve value for missing non-null list item.');
                    }

                    $resolvedValues[] = null;
                } else {
                    $resolvedValues[] = $this->fromAST($value, $itemType, $variables);
                }
            }

            return $resolvedValues;
        }

        return [$this->fromAST($node, $itemType, $variables)];
    }

    /**
     * @param NodeInterface|ValueNodeInterface $node
     * @param InputObjectType                  $type
     * @param array                            $variables
     * @return array|null
     * @throws InvalidTypeException
     * @throws InvariantException
     * @throws ResolutionException
     */
    protected function resolveInputObjectType(NodeInterface $node, InputObjectType $type, array $variables = [])
    {
        if (!$node instanceof ObjectValueNode) {
            throw new ResolutionException('Input object values can only be resolved form object value nodes.');
        }

        $resolvedValues = [];

        /** @var ObjectFieldNode[] $fieldNodes */
        $fieldNodes = keyMap($node->getFields(), function (ObjectFieldNode $value) {
            return $value->getNameValue();
        });

        foreach ($type->getFields() as $field) {
            $name      = $field->getName();
            $fieldNode = $fieldNodes[$name] ?? null;

            if (null === $fieldNode || $this->isMissingVariable($fieldNode->getValue(), $variables)) {
                if (null !== $field->getDefaultValue()) {
                    $resolvedValues[$name] = $field->getDefaultValue();
                } elseif ($field->getType() instanceof NonNullType) {
                    throw new ResolutionException('Cannot resolve input object value for missing non-null field.');
                }
                continue;
            }

            $fieldValue = $this->fromAST($fieldNode->getValue(), $field->getType(), $variables);

            $resolvedValues[$name] = $fieldValue;
        }

        return $resolvedValues;
    }

    /**
     * @param NodeInterface|ValueNodeInterface $node
     * @param EnumType                         $type
     * @return mixed|null
     * @throws InvariantException
     * @throws ResolutionException
     */
    protected function resolveEnumType(NodeInterface $node, EnumType $type)
    {
        if (!$node instanceof EnumValueNode) {
            throw new ResolutionException('Enum values can only be resolved from enum value nodes.');
        }

        $name = $node->getValue();

        $enumValue = $type->getValue($name);

        if (null === $enumValue) {
            throw new ResolutionException(\sprintf('Cannot resolve enum value for missing value "%s".', $name));
        }

        return $enumValue->getValue();
    }

    /**
     * @param NodeInterface|ValueNodeInterface $node
     * @param ScalarType                       $type
     * @param array                            $variables
     * @return mixed|null
     * @throws ResolutionException
     */
    protected function resolveScalarType(NodeInterface $node, ScalarType $type, array $variables = [])
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
            throw new ResolutionException(\sprintf('Failed to parse literal for scalar type "%s".', (string)$type));
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
        return $node instanceof VariableNode && !isset($variables[$node->getNameValue()]);
    }
}
