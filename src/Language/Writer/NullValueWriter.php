<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\NullValueNode;

class NullValueWriter extends AbstractWriter
{
    /**
     * @param NodeInterface|NullValueNode $node
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        return 'null';
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof NullValueNode;
    }
}
