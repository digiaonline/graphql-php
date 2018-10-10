<?php

namespace Digia\GraphQL\Language\Visitor;

use Digia\GraphQL\Language\Node\NodeInterface;

class VisitorResult
{

    public const ACTION_NO_ACTION = 'NO_ACTION';
    public const ACTION_BREAK     = 'BREAK';
    public const ACTION_REPLACE   = 'REPLACE';

    /**
     * @var NodeInterface|null
     */
    protected $value;

    /**
     * @var string
     */
    protected $action;

    /**
     * @param NodeInterface|null $value
     * @param string             $action
     */
    public function __construct(?NodeInterface $value = null, string $action = self::ACTION_NO_ACTION)
    {
        $this->value  = $value;
        $this->action = $action;
    }

    /**
     * @return NodeInterface|null
     */
    public function getValue(): ?NodeInterface
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     * @return self
     */
    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }
}
