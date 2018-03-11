<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\Node\ListTypeNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NonNullTypeNode;
use function Digia\GraphQL\Language\wrap;

class NullTypeWriter extends AbstractWriter
{
    /**
     * @param NodeInterface|NonNullTypeNode $node
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        return $this->printNode($node->getType()) . '!';
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof NonNullTypeNode;
    }
}
