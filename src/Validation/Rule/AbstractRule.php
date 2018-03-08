<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Visitor\AcceptVisitorTrait;
use Digia\GraphQL\Language\AST\Visitor\VisitorInterface;
use Digia\GraphQL\Util\SerializationInterface;

abstract class AbstractRule implements RuleInterface, VisitorInterface
{
    use ContextAwareTrait;

    /**
     * @param NodeInterface|AcceptVisitorTrait $node
     * @param string|int|null                  $key
     * @param NodeInterface|null               $parent
     * @param array                            $path
     * @return NodeInterface|SerializationInterface|null
     */
    public function enterNode(
        NodeInterface $node,
        $key = null,
        ?NodeInterface $parent = null,
        array $path = []
    ): ?NodeInterface {
        return $node;
    }

    /**
     * @param NodeInterface|AcceptVisitorTrait $node
     * @param string|int|null                  $key
     * @param NodeInterface|null               $parent
     * @param array                            $path
     * @return NodeInterface|SerializationInterface|null
     */
    public function leaveNode(
        NodeInterface $node,
        $key = null,
        ?NodeInterface $parent = null,
        array $path = []
    ): ?NodeInterface {
        return $node;
    }
}
