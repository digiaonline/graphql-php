<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Language\AST\Node\NodeInterface;

class Printer implements PrinterInterface
{

    /**
     * @inheritdoc
     */
    public function print(NodeInterface $node): string
    {
        // TODO: Implement proper printing of AST trees
        return $node->toJSON();
    }
}
