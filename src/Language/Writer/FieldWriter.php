<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use function Digia\GraphQL\Language\wrap;

class FieldWriter extends AbstractWriter
{
    /**
     * @param NodeInterface|FieldNode $node
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        $alias        = $this->printNode($node->getAlias());
        $name         = $this->printNode($node->getName());
        $arguments    = $this->printNodes($node->getArguments());
        $directives   = $this->printNodes($node->getDirectives());
        $selectionSet = $this->printNode($node->getSelectionSet());

        return implode(' ', [
            wrap('', $alias, ': ') . $name . wrap('(', implode(', ', $arguments), ')'),
            implode(' ', $directives),
            $selectionSet,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof FieldNode;
    }
}
