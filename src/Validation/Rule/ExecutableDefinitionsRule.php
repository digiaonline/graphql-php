<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\DefinitionNodeInterface;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\ExecutableDefinitionNodeInterface;
use Digia\GraphQL\Language\Node\NameAwareInterface;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\SchemaDefinitionNode;
use Digia\GraphQL\Language\Node\SchemaExtensionNode;
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
    protected function enterDocument(DocumentNode $node): ?NodeInterface
    {
        foreach ($node->getDefinitions() as $definition) {
            if (!$definition instanceof ExecutableDefinitionNodeInterface) {
                $this->context->reportError(
                    new ValidationException(
                        nonExecutableDefinitionMessage($this->getDefinitionName($definition)),
                        [$definition]
                    )
                );
            }
        }

        return $node;
    }

    /**
     * @param NodeInterface $node
     * @return null|string
     */
    protected function getDefinitionName(NodeInterface $node): ?string
    {
        if ($node instanceof SchemaDefinitionNode || $node instanceof SchemaExtensionNode) {
            return 'schema';
        }

        return $node instanceof NameAwareInterface
            ? $node->getNameValue()
            : null;
    }
}
