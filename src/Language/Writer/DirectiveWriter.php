<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\Node\DirectiveNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use function Digia\GraphQL\Language\wrap;

class DirectiveWriter extends AbstractWriter
{
    /**
     * @param NodeInterface|DirectiveNode $node
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        $name      = $this->printNode($node->getName());
        $arguments = $this->printNodes($node->getArguments());

        return '@' . $name . wrap('(', implode(', ', $arguments), ')');
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof DirectiveNode;
    }
}
