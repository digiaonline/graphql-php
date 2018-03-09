<?php

namespace Digia\GraphQL\Util;

use Digia\GraphQL\Language\AST\Node\ListTypeNode;
use Digia\GraphQL\Language\AST\Node\NamedTypeNode;
use Digia\GraphQL\Language\AST\Node\NonNullTypeNode;
use Digia\GraphQL\Language\AST\Node\TypeNodeInterface;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\SchemaInterface;
use function Digia\GraphQL\Type\GraphQLList;
use function Digia\GraphQL\Type\GraphQLNonNull;

/**
 * @param SchemaInterface   $schema
 * @param TypeNodeInterface $typeNode
 * @return TypeInterface|null
 * @throws \Exception
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

    throw new \Exception(sprintf('Unexpected type kind: %s', $typeNode->getKind()));
}
