<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\NodeInterface;

class ArgumentWriter extends AbstractWriter
{
    /**
     * @param NodeInterface|ArgumentNode $node
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        $name  = $this->printNode($node->getName());
        $value = $this->printNode($node->getValue());

        return $name . ': ' . $value;
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof ArgumentNode;
    }
}
