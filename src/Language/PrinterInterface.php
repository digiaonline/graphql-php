<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Language\AST\Node\NodeInterface;

interface PrinterInterface
{

    /**
     * @param NodeInterface $node
     * @return string
     */
    public function print(NodeInterface $node): string;
}
