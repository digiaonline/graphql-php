<?php

// TODO: Move this file under the Node namespace

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Language\Node\NodeInterface;

interface NodePrinterInterface
{
    /**
     * @param NodeInterface $node
     * @return string
     */
    public function print(NodeInterface $node): string;
}
