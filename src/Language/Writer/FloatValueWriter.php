<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\Node\FloatValueNode;
use Digia\GraphQL\Language\Node\NodeInterface;

class FloatValueWriter extends AbstractWriter
{

    /**
     * @param NodeInterface|FloatValueNode $node
     *
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
        return $node instanceof FloatValueNode;
    }
}
