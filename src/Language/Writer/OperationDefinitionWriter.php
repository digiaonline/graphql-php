<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use function Digia\GraphQL\Language\wrap;

class OperationDefinitionWriter extends AbstractWriter
{
    /**
     * @param NodeInterface|OperationDefinitionNode $node
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        $operation            = $node->getOperation();
        $name                 = $this->printNode($node->getName());
        $variablesDefinitions = $this->printNodes($node->getVariableDefinitions());
        $directives           = $this->printNodes($node->getDirectives());
        $selectionSet         = $this->printNode($node->getSelectionSet());

        // Anonymous queries with no directives or variable definitions can use
        // the query short form.
        return null === $name && empty($directives) && empty($variablesDefinitions) && $operation === 'query'
            ? $selectionSet
            : implode(' ', [
                $operation,
                $name . wrap('(', implode(', ', $variablesDefinitions), ')'),
                implode(' ', $directives),
                $selectionSet,
            ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof OperationDefinitionNode;
    }
}
