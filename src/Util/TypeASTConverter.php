<?php

namespace Digia\GraphQL\Util;

use Digia\GraphQL\Util\ConversionException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ListTypeNode;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\NonNullTypeNode;
use Digia\GraphQL\Language\Node\TypeNodeInterface;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Type\Definition\TypeInterface;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newNonNull;

class TypeASTConverter
{
    /**
     * Given a Schema and an AST node describing a type, return a GraphQLType
     * definition which applies to that type. For example, if provided the parsed
     * AST node for `[User]`, a GraphQLList instance will be returned, containing
     * the type called "User" found in the schema. If a type called "User" is not
     * found in the schema, then undefined will be returned.
     *
     * @param Schema            $schema
     * @param TypeNodeInterface $typeNode
     * @return TypeInterface|null
     * @throws InvariantException
     * @throws ConversionException
     */
    public static function convert(Schema $schema, TypeNodeInterface $typeNode): ?TypeInterface
    {
        $innerType = null;

        if ($typeNode instanceof ListTypeNode) {
            $innerType = self::convert($schema, $typeNode->getType());
            return null !== $innerType ? newList($innerType) : null;
        }

        if ($typeNode instanceof NonNullTypeNode) {
            $innerType = self::convert($schema, $typeNode->getType());
            return null !== $innerType ? newNonNull($innerType) : null;
        }

        if ($typeNode instanceof NamedTypeNode) {
            return $schema->getType($typeNode->getNameValue());
        }

        throw new ConversionException(sprintf('Unexpected type kind: %s', $typeNode->getKind()));
    }
}
