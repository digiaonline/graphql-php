<?php

namespace Digia\GraphQL\Language\Visitor;

use Digia\GraphQL\Language\Node\NodeInterface;

class Visitor implements VisitorInterface
{
    /**
     * @var callable|null
     */
    protected $enterCallback;

    /**
     * @var callable|null
     */
    protected $leaveCallback;

    /**
     * Visitor constructor.
     *
     * @param callable|null $enterCallback
     * @param callable|null $leaveCallback
     */
    public function __construct(?callable $enterCallback = null, ?callable $leaveCallback = null)
    {
        $this->enterCallback = $enterCallback;
        $this->leaveCallback = $leaveCallback;
    }

    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): VisitorResult
    {
        return null !== $this->enterCallback
            ? \call_user_func($this->enterCallback, $node)
            : new VisitorResult($node);
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(NodeInterface $node): VisitorResult
    {
        return null !== $this->leaveCallback
            ? \call_user_func($this->leaveCallback, $node)
            : new VisitorResult($node);
    }
}
