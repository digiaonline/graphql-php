<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\ExecutableDefinitionNodeInterface;
use Digia\GraphQL\Language\AST\Node\NamedTypeNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\SchemaDefinitionNode;

/**
 * Executable definitions
 *
 * A GraphQL document is only valid for execution if all definitions are either
 * operation or fragment definitions.
 */
class ExecutableDefinitionRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof DocumentNode) {
            /** @var NamedTypeNode $definition */
            foreach ($node->getDefinitions() as $definition) {
                if (!$definition instanceof ExecutableDefinitionNodeInterface) {
                    $this->context->reportError(
                        new ValidationException(
                            nonExecutableDefinitionMessage(
                                $definition instanceof SchemaDefinitionNode ? 'schema' : $definition->getNameValue()
                            ),
                            [$definition]
                        )
                    );
                }
            }
        }

        return $node;
    }
}
