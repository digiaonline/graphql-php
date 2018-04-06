<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\Node\EnumValueNode;
use Digia\GraphQL\Language\Node\NodeInterface;

class EnumValueWriter extends AbstractWriter
{

    /**
     * @param NodeInterface|EnumValueNode $node
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
        return $node instanceof EnumValueNode;
    }
}
