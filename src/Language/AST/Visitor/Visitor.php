<?php

namespace Digia\GraphQL\Language\AST\Visitor;

use Digia\GraphQL\Language\AST\Node\NodeInterface;

class Visitor implements VisitorInterface
{

    /**
     * @var callable|null
     */
    protected $enterFunction;

    /**
     * @var callable|null
     */
    protected $leaveFunction;

    /**
     * TestableVisitor constructor.
     * @param callable|null $enterFunction
     * @param callable|null $leaveFunction
     */
    public function __construct(?callable $enterFunction = null, ?callable $leaveFunction = null)
    {
        $this->enterFunction = $enterFunction;
        $this->leaveFunction = $leaveFunction;
    }

    /**
     * @inheritdoc
     */
    public function enterNode(
        NodeInterface $node,
        $key = null,
        ?NodeInterface $parent = null,
        array $path = [],
        array $ancestors = []
    ): ?NodeInterface {
        return null !== $this->enterFunction
            ? \call_user_func($this->enterFunction, $node, $key, $parent, $path, $ancestors)
            : $node;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(
        NodeInterface $node,
        $key = null,
        ?NodeInterface $parent = null,
        array $path = [],
        array $ancestors = []
    ): ?NodeInterface {
        return null !== $this->leaveFunction
            ? \call_user_func($this->leaveFunction, $node, $key, $parent, $path, $ancestors)
            : $node;
    }
}
