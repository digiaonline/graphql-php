<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

/**
 * @param NodeInterface $node
 * @return string
 */
function printNode(NodeInterface $node): string
{
    static $instance = null;

    if (null === $instance) {
        $instance = new Printer();
    }

    return $instance->print($node);
}
