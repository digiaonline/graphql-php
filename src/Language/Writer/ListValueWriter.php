<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\Node\ListValueNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use function Digia\GraphQL\Language\wrap;

class ListValueWriter extends AbstractWriter
{

    /**
     * @param NodeInterface|ListValueNode $node
     *
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        $values = $this->printNodes($node->getValues());

        return wrap('[', implode(', ', $values), ']');
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof ListValueNode;
    }
}
