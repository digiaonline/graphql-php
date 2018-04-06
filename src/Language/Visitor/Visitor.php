<?php

namespace Digia\GraphQL\Language\Visitor;

use Digia\GraphQL\Language\Node\NodeInterface;

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
     *
     * @param callable|null $enterFunction
     * @param callable|null $leaveFunction
     */
    public function __construct(
        ?callable $enterFunction = null,
        ?callable $leaveFunction = null
    ) {
        $this->enterFunction = $enterFunction;
        $this->leaveFunction = $leaveFunction;
    }

    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        return null !== $this->enterFunction
            ? \call_user_func($this->enterFunction, $node)
            : $node;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(NodeInterface $node): ?NodeInterface
    {
        return null !== $this->leaveFunction
            ? \call_user_func($this->leaveFunction, $node)
            : $node;
    }
}
