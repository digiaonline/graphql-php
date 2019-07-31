<?php

namespace Digia\GraphQL\Util;

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
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\TypeInterface;

class ValueASTConverter
{
    /**
     * Produces a PHP value given a GraphQL Value AST.
     *
     * A GraphQL type must be provided, which will be used to interpret different
     * GraphQL Value literals.
     *
     * Throws a `ConversionException` when the value could not be validly converted according to
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
     * @param NodeInterface $node
     * @param TypeInterface $type
     * @param array         $variables
     * @return mixed|null
     * @throws InvalidTypeException
     * @throws InvariantException
     * @throws ConversionException
     */
    public static function convert(NodeInterface $node, TypeInterface $type, array $variables = [])
    {
        if ($type instanceof NonNullType) {
            return self::convertNonNullType($node, $type, $variables);
        }

        if ($node instanceof NullValueNode) {
            return null;
        }

        if ($node instanceof VariableNode) {
            return self::convertVariable($node, $type, $variables);
        }

        if ($type instanceof ListType) {
            return self::convertListType($node, $type, $variables);
        }

        if ($type instanceof InputObjectType) {
            return self::convertInputObjectType($node, $type, $variables);
        }

        if ($type instanceof EnumType) {
            return self::convertEnumType($node, $type);
        }

        if ($type instanceof ScalarType) {
            return self::convertScalarType($node, $type, $variables);
        }

        throw new InvalidTypeException(\sprintf('Unknown type: %s', (string)$type));
    }

    /**
     * @param NodeInterface|ValueNodeInterface $node
     * @param NonNullType                      $type
     * @param array                            $variables
     * @return mixed|null
     * @throws InvalidTypeException
     * @throws InvariantException
     * @throws ConversionException
     */
    protected static function convertNonNullType(NodeInterface $node, NonNullType $type, array $variables)
    {
        if ($node instanceof NullValueNode) {
            throw new ConversionException('Cannot convert non-null values from null value node.');
        }

        return self::convert($node, $type->getOfType(), $variables);
    }

    /**
     * @param VariableNode  $node
     * @param TypeInterface $type
     * @param array         $variables
     * @return mixed
     * @throws ConversionException
     */
    protected static function convertVariable(VariableNode $node, TypeInterface $type, array $variables)
    {
        $variableName = $node->getNameValue();

        if (!isset($variables[$variableName])) {
            throw new ConversionException(
                \sprintf('Cannot convert value for missing variable "%s".', $variableName)
            );
        }

        // Note: we're not doing any checking that this variable is correct. We're
        // assuming that this query has been validated and the variable usage here
        // is of the correct type.
        $variableValue = $variables[$variableName];

        if (null === $variableValue && $type instanceof NonNullType) {
            throw new ConversionException(
                \sprintf('Cannot convert invalid value "%s" for variable "%s".', $variableValue, $variableName)
            );
        }

        return $variableValue;
    }

    /**
     * @param NodeInterface|ValueNodeInterface $node
     * @param ListType                         $type
     * @param array                            $variables
     * @return array|null
     * @throws ConversionException
     * @throws InvalidTypeException
     * @throws InvariantException
     */
    protected static function convertListType(NodeInterface $node, ListType $type, array $variables): ?array
    {
        $itemType = $type->getOfType();

        if ($node instanceof ListValueNode) {
            $values = [];

            foreach ($node->getValues() as $value) {
                if (self::isMissingVariable($value, $variables)) {
                    if ($itemType instanceof NonNullType) {
                        // If an array contains a missing variable, it is either converted to
                        // null or if the item type is non-null, it considered invalid.
                        throw new ConversionException('Cannot convert value for missing non-null list item.');
                    }

                    $values[] = null;
                } else {
                    $values[] = self::convert($value, $itemType, $variables);
                }
            }

            return $values;
        }

        return [self::convert($node, $itemType, $variables)];
    }

    /**
     * @param NodeInterface|ValueNodeInterface $node
     * @param InputObjectType                  $type
     * @param array                            $variables
     * @return array|null
     * @throws InvalidTypeException
     * @throws InvariantException
     * @throws ConversionException
     */
    protected static function convertInputObjectType(
        NodeInterface $node,
        InputObjectType $type,
        array $variables
    ): ?array {
        if (!$node instanceof ObjectValueNode) {
            throw new ConversionException('Input object values can only be converted form object value nodes.');
        }

        $values = [];

        /** @var ObjectFieldNode[] $fieldNodes */
        $fieldNodes = keyMap($node->getFields(), function (ObjectFieldNode $value) {
            return $value->getNameValue();
        });

        foreach ($type->getFields() as $field) {
            $name      = $field->getName();
            $fieldNode = $fieldNodes[$name] ?? null;

            if (null === $fieldNode || self::isMissingVariable($fieldNode->getValue(), $variables)) {
                if (null !== $field->getDefaultValue()) {
                    $values[$name] = $field->getDefaultValue();
                } elseif ($field->getType() instanceof NonNullType) {
                    throw new ConversionException('Cannot convert input object value for missing non-null field.');
                }
                continue;
            }

            $fieldValue = self::convert($fieldNode->getValue(), $field->getType(), $variables);

            $values[$name] = $fieldValue;
        }

        return $values;
    }

    /**
     * @param NodeInterface|ValueNodeInterface $node
     * @param EnumType                         $type
     * @return mixed|null
     * @throws InvariantException
     * @throws ConversionException
     */
    protected static function convertEnumType(NodeInterface $node, EnumType $type)
    {
        if (!$node instanceof EnumValueNode) {
            throw new ConversionException('Enum values can only be converted from enum value nodes.');
        }

        $name = $node->getValue();

        $enumValue = $type->getValue($name);

        if (null === $enumValue) {
            throw new ConversionException(\sprintf('Cannot convert enum value for missing value "%s".', $name));
        }

        return $enumValue->getValue();
    }

    /**
     * @param NodeInterface|ValueNodeInterface $node
     * @param ScalarType                       $type
     * @param array                            $variables
     * @return mixed|null
     * @throws ConversionException
     */
    protected static function convertScalarType(NodeInterface $node, ScalarType $type, array $variables)
    {
        // Scalars fulfill parsing a literal value via parseLiteral().
        // Invalid values represent a failure to parse correctly, in which case
        // no value is returned.
        try {
            $result = $type->parseLiteral($node, $variables);
        } catch (\Exception $e) {
            // This will be handled by the next if-statement.
        }

        if (!isset($result)) {
            throw new ConversionException(\sprintf('Failed to parse literal for scalar type "%s".', (string)$type));
        }

        return $result;
    }

    /**
     * @param ValueNodeInterface $node
     * @param array              $variables
     * @return bool
     */
    protected static function isMissingVariable(ValueNodeInterface $node, array $variables): bool
    {
        return $node instanceof VariableNode && !isset($variables[$node->getNameValue()]);
    }
}
