<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\AST\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use function Digia\GraphQL\Language\wrap;

class FragmentDefinitionWriter extends AbstractWriter
{
    /**
     * @param NodeInterface|FragmentDefinitionNode $node
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        $name                = $this->printNode($node->getName());
        $typeCondition       = $this->printNode($node->getTypeCondition());
        $variableDefinitions = $this->printNodes($node->getVariableDefinitions());
        $directives          = $this->printNodes($node->getDirectives());
        $selectionSet        = $this->printNode($node->getSelectionSet());

        // Note: fragment variable definitions are experimental and may be changed
        // or removed in the future.
        return implode(' ', [
            'fragment ' . $name . wrap('(', implode(', ', $variableDefinitions), ')'),
            'on ' . $typeCondition . ' ' . implode(' ', $directives),
            $selectionSet
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof FragmentDefinitionNode;
    }
}
