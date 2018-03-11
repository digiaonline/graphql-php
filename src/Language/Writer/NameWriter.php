<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\AST\Node\NameNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;

class NameWriter extends AbstractWriter
{
    /**
     * @param NodeInterface|NameNode $node
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        return $node->getValue();
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof NameNode;
    }
}
