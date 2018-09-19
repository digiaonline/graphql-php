<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use function Digia\GraphQL\Validation\unusedFragmentMessage;

/**
 * No unused fragments
 *
 * A GraphQL document is only valid if all fragment definitions are spread
 * within operations, or spread within other fragments spread within operations.
 */
class NoUnusedFragmentsRule extends AbstractRule
{
    /**
     * @var OperationDefinitionNode[]
     */
    protected $operationDefinitions = [];

    /**
     * @var FragmentDefinitionNode[]
     */
    protected $fragmentDefinitions = [];

    /**
     * @inheritdoc
     */
    protected function enterOperationDefinition(OperationDefinitionNode $node): VisitorResult
    {
        $this->operationDefinitions[] = $node;

        return new VisitorResult(null);
    }

    /**
     * @inheritdoc
     */
    protected function enterFragmentDefinition(FragmentDefinitionNode $node): VisitorResult
    {
        $this->fragmentDefinitions[] = $node;

        return new VisitorResult(null);
    }

    /**
     * @inheritdoc
     */
    protected function leaveDocument(DocumentNode $node): VisitorResult
    {
        $fragmentNamesUsed = [];

        foreach ($this->operationDefinitions as $operationDefinition) {
            foreach ($this->context->getRecursivelyReferencedFragments($operationDefinition) as $fragmentDefinition) {
                $fragmentNamesUsed[$fragmentDefinition->getNameValue()] = true;
            }
        }

        foreach ($this->fragmentDefinitions as $fragmentDefinition) {
            $fragmentName = $fragmentDefinition->getNameValue();

            if (!isset($fragmentNamesUsed[$fragmentName])) {
                $this->context->reportError(
                    new ValidationException(unusedFragmentMessage($fragmentName), [$fragmentDefinition])
                );
            }
        }

        return new VisitorResult($node);
    }
}
