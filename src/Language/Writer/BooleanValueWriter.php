<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\AST\Node\BooleanValueNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;

class BooleanValueWriter extends AbstractWriter
{
    /**
     * @param NodeInterface|BooleanValueNode $node
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        return $node->getValue() ? 'true' : 'false';
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof BooleanValueNode;
    }
}
