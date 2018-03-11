<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\Node\IntValueNode;
use Digia\GraphQL\Language\Node\NodeInterface;

class IntValueWriter extends AbstractWriter
{
    /**
     * @param NodeInterface|IntValueNode $node
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
        return $node instanceof IntValueNode;
    }
}
