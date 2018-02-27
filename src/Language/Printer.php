<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\Contract\PrinterInterface;

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
