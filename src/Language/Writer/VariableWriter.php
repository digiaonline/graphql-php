<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\VariableNode;

class VariableWriter extends AbstractWriter
{
    /**
     * @param NodeInterface|VariableNode $node
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        return '$' . $node->getName();
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof VariableNode;
    }
}
