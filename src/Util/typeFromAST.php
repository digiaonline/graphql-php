<?php

namespace Digia\GraphQL\Util;

use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Language\Node\ListTypeNode;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\NonNullTypeNode;
use Digia\GraphQL\Language\Node\TypeNodeInterface;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\SchemaInterface;
use function Digia\GraphQL\Type\GraphQLList;
use function Digia\GraphQL\Type\GraphQLNonNull;

/**
 * @param SchemaInterface   $schema
 * @param TypeNodeInterface $typeNode
 * @return TypeInterface|null
 * @throws InvalidTypeException
 */
function typeFromAST(SchemaInterface $schema, TypeNodeInterface $typeNode): ?TypeInterface
{
    $innerType = null;

    if ($typeNode instanceof ListTypeNode) {
        $innerType = typeFromAST($schema, $typeNode->getType());
        return null !== $innerType ? GraphQLList($innerType) : null;
    }

    if ($typeNode instanceof NonNullTypeNode) {
        $innerType = typeFromAST($schema, $typeNode->getType());
        return null !== $innerType ? GraphQLNonNull($innerType) : null;
    }

    if ($typeNode instanceof NamedTypeNode) {
        return $schema->getType($typeNode->getNameValue());
    }

    throw new InvalidTypeException(sprintf('Unexpected type kind: %s', $typeNode->getKind()));
}
