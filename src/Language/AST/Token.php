<?php

namespace Digia\GraphQL\Language\AST;

use Digia\GraphQL\ConfigObject;

class Token extends ConfigObject
{

    /**
     * @var string
     */
    private $kind;

    /**
     * @var int
     */
    private $start;

    /**
     * @var int
     */
    private $end;

    /**
     * @var int
     */
    private $line;

    /**
     * @var int
     */
    private $column;

    /**
     * @var ?string
     */
    private $value;

    /**
     * @var ?Token
     */
    private $prev;

    /**
     * @var ?Token
     */
    private $next;

    /**
     * @return string
     */
    public function getKind(): string
    {
        return $this->kind;
    }

    /**
     * @return int
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * @return int
     */
    public function getEnd(): int
    {
        return $this->end;
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @return int
     */
    public function getColumn(): int
    {
        return $this->column;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return Token|null
     */
    public function getPrev(): ?Token
    {
        return $this->prev;
    }

    /**
     * @return Token|null
     */
    public function getNext(): ?Token
    {
        return $this->next;
    }
}
