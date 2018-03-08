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
    use ContextAwareTrait;

    /**
     * @inheritdoc
     */
    public function enterNode(
        NodeInterface $node,
        $key = null,
        ?NodeInterface $parent = null,
        array $path = []
    ): ?NodeInterface {
        if ($node instanceof DocumentNode) {
            /** @var NamedTypeNode $definition */
            foreach ($node->getDefinitions() as $definition) {
                if (!$definition instanceof ExecutableDefinitionNodeInterface) {
                    $this->context->reportError(
                        new GraphQLError(
                            sprintf(
                                'The %s definition is not executable.',
                                $definition instanceof SchemaDefinitionNode ? 'schema' : $definition->getNameValue()
                            ),
                            [$definition]
                        )
                    );
                }
            }

            return null;
        }

        return $node;
    }
}
