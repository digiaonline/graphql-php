<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\ExecutableDefinitionNodeInterface;
use Digia\GraphQL\Language\AST\Node\NamedTypeNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\SchemaDefinitionNode;

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
                        new GraphQLError(
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
