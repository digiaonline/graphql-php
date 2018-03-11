<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\AST\Node\NamedTypeNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;

class NamedTypeWriter extends AbstractWriter
{
    /**
     * @param NodeInterface|NamedTypeNode $node
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        return $this->printNode($node->getName());
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof NamedTypeNode;
    }
}
