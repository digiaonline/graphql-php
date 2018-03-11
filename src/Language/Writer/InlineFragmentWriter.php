<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\Node\InlineFragmentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use function Digia\GraphQL\Language\wrap;

class InlineFragmentWriter extends AbstractWriter
{
    /**
     * @param NodeInterface|InlineFragmentNode $node
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        $typeCondition = $this->printNode($node->getTypeCondition());
        $directives    = $this->printNodes($node->getDirectives());
        $selectionSet  = $this->printNode($node->getSelectionSet());

        return implode(' ', ['...', wrap('on ', $typeCondition), implode(' ', $directives), $selectionSet]);
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof InlineFragmentNode;
    }
}
