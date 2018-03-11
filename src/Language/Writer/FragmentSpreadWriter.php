<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use function Digia\GraphQL\Language\wrap;

class FragmentSpreadWriter extends AbstractWriter
{
    /**
     * @param NodeInterface|FragmentSpreadNode $node
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        $name       = $this->printNode($node->getName());
        $directives = $this->printNodes($node->getDirectives());

        return '...' . $name . wrap(' ', implode(' ', $directives));
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof FragmentSpreadNode;
    }
}
