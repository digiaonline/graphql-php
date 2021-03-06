<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Util\ArrayToJsonTrait;
use Digia\GraphQL\Util\SerializationInterface;

class Token implements SerializationInterface
{
    use ArrayToJsonTrait;

    /**
     * @var string
     */
    protected $kind;

    /**
     * @var int
     */
    protected $start;

    /**
     * @var int
     */
    protected $end;

    /**
     * @var int
     */
    protected $line;

    /**
     * @var int
     */
    protected $column;

    /**
     * @var Token|null
     */
    protected $prev;

    /**
     * @var Token|null
     */
    protected $next;

    /**
     * @var string|null
     */
    protected $value;

    /**
     * Token constructor.
     *
     * @param string      $kind
     * @param int         $start
     * @param int         $end
     * @param int         $line
     * @param int         $column
     * @param Token|null  $prev
     * @param string|null $value
     */
    public function __construct(
        string $kind,
        int $start = 0,
        int $end = 0,
        int $line = 0,
        int $column = 0,
        ?Token $prev = null,
        ?string $value = null
    ) {
        $this->kind   = $kind;
        $this->start  = $start;
        $this->end    = $end;
        $this->line   = $line;
        $this->column = $column;
        $this->prev   = $prev;
        $this->value  = $value;
    }

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

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param Token|null $next
     * @return $this
     */
    public function setNext(?Token $next)
    {
        $this->next = $next;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'   => $this->kind,
            'line'   => $this->line,
            'column' => $this->column,
            'value'  => $this->value,
        ];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value !== null
            ? sprintf('%s "%s"', $this->kind, $this->value)
            : $this->kind;
    }
}
