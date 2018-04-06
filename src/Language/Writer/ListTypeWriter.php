<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\Node\ListTypeNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use function Digia\GraphQL\Language\wrap;

class ListTypeWriter extends AbstractWriter
{

    /**
     * @param NodeInterface|ListTypeNode $node
     *
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        return wrap('[', $this->printNode($node->getType()), ']');
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof ListTypeNode;
    }
}
