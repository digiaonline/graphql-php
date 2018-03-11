<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\AST\Node\InputObjectTypeDefinitionNode;
use Digia\GraphQL\Language\AST\Node\InterfaceTypeDefinitionNode;
use Digia\GraphQL\Language\AST\Node\NamedTypeNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\ObjectTypeDefinitionNode;
use Digia\GraphQL\Language\AST\Node\UnionTypeDefinitionNode;
use function Digia\GraphQL\Util\suggestionList;

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
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof ObjectTypeDefinitionNode || $node instanceof InterfaceTypeDefinitionNode || $node instanceof UnionTypeDefinitionNode || $node instanceof InputObjectTypeDefinitionNode) {
            // TODO: when validating IDL, re-enable these. Experimental version does not add unreferenced types, resulting in false-positive errors. Squelched errors for now.
            return null;
        }

        if ($node instanceof NamedTypeNode) {
            $schema   = $this->validationContext->getSchema();
            $typeName = $node->getNameValue();
            $type     = $schema->getType($typeName);

            if (null === $type) {
                $this->validationContext->reportError(
                    new ValidationException(
                        unknownTypeMessage($typeName, suggestionList($typeName, array_keys($schema->getTypeMap()))),
                        [$node]
                    )
                );
            }
        }

        return $node;
    }
}
