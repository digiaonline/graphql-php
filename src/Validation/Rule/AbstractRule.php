<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Visitor\VisitorInterface;
use Digia\GraphQL\Validation\ValidationContextAwareTrait;

abstract class AbstractRule implements RuleInterface, VisitorInterface
{
    use ValidationContextAwareTrait;

    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(NodeInterface $node): ?NodeInterface
    {
        return $node;
    }
}
