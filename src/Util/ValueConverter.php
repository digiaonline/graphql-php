<?php

namespace Digia\GraphQL\Util;

use Digia\GraphQL\Util\ConversionException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\SyntaxErrorException;
use Digia\GraphQL\Language\Node\BooleanValueNode;
use Digia\GraphQL\Language\Node\EnumValueNode;
use Digia\GraphQL\Language\Node\FloatValueNode;
use Digia\GraphQL\Language\Node\IntValueNode;
use Digia\GraphQL\Language\Node\ListValueNode;
use Digia\GraphQL\Language\Node\NameNode;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\NullValueNode;
use Digia\GraphQL\Language\Node\ObjectFieldNode;
use Digia\GraphQL\Language\Node\ObjectValueNode;
use Digia\GraphQL\Language\Node\StringValueNode;
use Digia\GraphQL\Language\Node\ValueNodeInterface;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\InputField;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\SerializableTypeInterface;
use Digia\GraphQL\Type\Definition\TypeInterface;
use function Digia\GraphQL\Type\idType;

class ValueConverter
{
    /**
     * Produces a GraphQL Value AST given a PHP value.
     *
     * A GraphQL type must be provided, which will be used to interpret different
     * PHP values.
     *
     * | JSON Value    | GraphQL Value        |
     * | ------------- | -------------------- |
     * | Object        | Input Object         |
     * | Array         | List                 |
     * | Boolean       | Boolean              |
     * | String        | String / Enum Value  |
     * | Number        | Int / Float          |
     * | Mixed         | Enum Value           |
     * | null          | NullValue            |
     *
     * @param mixed         $value
     * @param TypeInterface $type
     * @return ValueNodeInterface|null
     * @throws InvariantException
     * @throws SyntaxErrorException
     * @throws ConversionException
     */
    public static function convert($value, TypeInterface $type): ?ValueNodeInterface
    {
        if ($type instanceof NonNullType) {
            $node = self::convert($value, $type->getOfType());

            return null !== $node && $node->getKind() === NodeKindEnum::NULL ? null : $node;
        }

        if (null === $value) {
            return new NullValueNode(null);
        }

        // Convert PHP array to GraphQL list. If the GraphQLType is a list, but
        // the value is not an array, convert the value using the list's item type.
        if ($type instanceof ListType) {
            return self::convertListType($value, $type);
        }

        if ($type instanceof ScalarType || $type instanceof EnumType) {
            return self::convertScalarOrEnum($value, $type);
        }

        throw new ConversionException(\sprintf('Unknown type: %s.', (string)$type));
    }

    /**
     * @param mixed    $value
     * @param ListType $type
     * @return ValueNodeInterface|null
     * @throws InvariantException
     * @throws SyntaxErrorException
     * @throws ConversionException
     */
    protected static function convertListType($value, ListType $type): ?ValueNodeInterface
    {
        $itemType = $type->getOfType();

        if (!\is_iterable($value)) {
            return self::convert($value, $itemType);
        }

        $nodes = [];

        foreach ($value as $item) {
            $itemNode = self::convert($item, $itemType);
            if (null !== $itemNode) {
                $nodes[] = $itemNode;
            }
        }

        return new ListValueNode($nodes, null);
    }

    /**
     * @param mixed                     $value
     * @param SerializableTypeInterface $type
     * @return ValueNodeInterface|null
     * @throws ConversionException
     */
    protected static function convertScalarOrEnum($value, SerializableTypeInterface $type): ?ValueNodeInterface
    {
        // Since value is an internally represented value, it must be serialized
        // to an externally represented value before converting into an AST.
        $serialized = $type->serialize($value);

        if (null === $serialized) {
            return null;
        }

        // Others serialize based on their corresponding JavaScript scalar types.
        if (\is_bool($serialized)) {
            return new BooleanValueNode($serialized, null);
        }

        if (\is_float($serialized)) {
            return new FloatValueNode($serialized, null);
        }

        if (\is_int($serialized)) {
            return new IntValueNode($serialized, null);
        }

        if (\is_string($serialized)) {
            // Enum types use Enum literals.
            if ($type instanceof EnumType) {
                return new EnumValueNode($serialized, null);
            }

            // ID types can use Int literals.
            if (idType() === $type && \is_numeric($serialized)) {
                return new IntValueNode($serialized, null);
            }

            return new StringValueNode($serialized, false, null);
        }

        throw new ConversionException(\sprintf('Cannot convert value to AST: %s', $serialized));
    }
}
