<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\ExecutableDefinitionNodeInterface;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\SchemaDefinitionNode;
use function Digia\GraphQL\Validation\nonExecutableDefinitionMessage;

/**
 * Executable definitions
 *
 * A GraphQL document is only valid for execution if all definitions are either
 * operation or fragment definitions.
 */
class ExecutableDefinitionsRule extends AbstractRule
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
                    $this->validationContext->reportError(
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
