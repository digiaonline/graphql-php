<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\ObjectValueNode;
use function Digia\GraphQL\Language\wrap;

class ObjectValueWriter extends AbstractWriter
{
    /**
     * @param NodeInterface|ObjectValueNode $node
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        $fields = $this->printNodes($node->getFields());

        return wrap('{', implode(', ', $fields), '}');
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof ObjectValueNode;
    }
}
