<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\NodeInterface;

class DocumentWriter extends AbstractWriter
{

    /**
     * @param NodeInterface|DocumentNode $node
     *
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        return implode("\n\n", $node->getDefinitions())."\n";
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof DocumentNode;
    }
}
