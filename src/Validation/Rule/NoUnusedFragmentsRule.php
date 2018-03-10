<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;

/**
 * No unused fragments
 *
 * A GraphQL document is only valid if all fragment definitions are spread
 * within operations, or spread within other fragments spread within operations.
 */
class NoUnusedFragmentsRule extends AbstractRule
{
    /**
     * @var array|OperationDefinitionNode[]
     */
    protected $operationDefinitions = [];

    /**
     * @var array|FragmentDefinitionNode[]
     */
    protected $fragmentDefinitions = [];

    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof OperationDefinitionNode) {
            $this->operationDefinitions[] = $node;
            return null;
        }

        if ($node instanceof FragmentDefinitionNode) {
            $this->fragmentDefinitions[] = $node;
            return null;
        }

        return $node;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof DocumentNode) {
            $fragmentNamesUsed = [];

            foreach ($this->operationDefinitions as $operationDefinition) {
                foreach ($this->validationContext->getRecursivelyReferencedFragments($operationDefinition) as $fragmentDefinition) {
                    $fragmentNamesUsed[$fragmentDefinition->getNameValue()] = true;
                }
            }

            foreach ($this->fragmentDefinitions as $fragmentDefinition) {
                $fragmentName = $fragmentDefinition->getNameValue();

                if (!isset($fragmentNamesUsed[$fragmentName])) {
                    $this->validationContext->reportError(
                        new ValidationException(unusedFragmentMessage($fragmentName), [$fragmentDefinition])
                    );
                }
            }
        }

        return $node;
    }
}