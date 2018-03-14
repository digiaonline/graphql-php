<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\InputObjectTypeDefinitionNode;
use Digia\GraphQL\Language\Node\InterfaceTypeDefinitionNode;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\NameNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\ObjectTypeDefinitionNode;
use Digia\GraphQL\Language\Node\UnionTypeDefinitionNode;
use function Digia\GraphQL\Util\suggestionList;
use function Digia\GraphQL\Validation\unknownTypeMessage;

/**
 * Known type names
 *
 * A GraphQL document is only valid if referenced types (specifically
 * variable definitions and fragment conditions) are defined by the type schema.
 */
class KnownTypeNamesRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    protected function enterNamedType(NamedTypeNode $node): ?NodeInterface
    {
        $schema   = $this->context->getSchema();
        $typeName = $node->getNameValue();
        $type     = $schema->getType($typeName);

        if (null === $type) {
            $this->context->reportError(
                new ValidationException(
                    unknownTypeMessage($typeName, suggestionList($typeName, \array_keys($schema->getTypeMap()))),
                    [$node]
                )
            );
        }

        return $node;
    }

    // TODO: when validating IDL, re-enable these. Experimental version does not add unreferenced types, resulting in false-positive errors. Squelched errors for now.

    /**
     * @inheritdoc
     */
    protected function enterObjectTypeDefinition(ObjectTypeDefinitionNode $node): ?NodeInterface
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    protected function enterInterfaceTypeDefinition(InterfaceTypeDefinitionNode $node): ?NodeInterface
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    protected function enterUnionTypeDefinition(UnionTypeDefinitionNode $node): ?NodeInterface
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    protected function enterInputObjectTypeDefinition(InputObjectTypeDefinitionNode $node): ?NodeInterface
    {
        return null;
    }
}
