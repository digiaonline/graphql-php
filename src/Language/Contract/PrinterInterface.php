<?php

namespace Digia\GraphQL\Language\Contract;

use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

interface PrinterInterface
{

    /**
     * @param NodeInterface $node
     * @return string
     */
    public function print(NodeInterface $node): string;
}
