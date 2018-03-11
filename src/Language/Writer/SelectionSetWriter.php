<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\SelectionSetNode;
use function Digia\GraphQL\Language\block;

class SelectionSetWriter extends AbstractWriter
{
    /**
     * @param NodeInterface|SelectionSetNode $node
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        return block($this->printNodes($node->getSelections()));
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof SelectionSetNode;
    }
}
