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
    public $kind;

    /**
     * @var int
     */
    public $start;

    /**
     * @var int
     */
    public $end;

    /**
     * @var int
     */
    public $line;

    /**
     * @var int
     */
    public $column;

    /**
     * @var ?Token
     */
    public $prev;

    /**
     * @var ?Token
     */
    public $next;

    /**
     * @var ?string
     */
    public $value;

    /**
     * Token constructor.
     *
     * @param string     $kind
     * @param int        $start
     * @param int        $end
     * @param int        $line
     * @param int        $column
     * @param Token|null $prev
     * @param null       $value
     */
    public function __construct(
        string $kind,
        int $start = 0,
        int $end = 0,
        int $line = 0,
        int $column = 0,
        ?Token $prev = null,
        $value = null
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
