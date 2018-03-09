<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Visitor\VisitorInterface;

abstract class AbstractRule implements RuleInterface, VisitorInterface
{
    use ContextAwareTrait;

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
