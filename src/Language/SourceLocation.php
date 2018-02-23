<?php

namespace Digia\GraphQL\Language;

class SourceLocation
{

    /**
     * @var int
     */
    protected $line;

    /**
     * @var int
     */
    protected $column;

    /**
     * SourceLocation constructor.
     *
     * @param int $line
     * @param int $column
     */
    public function __construct(int $line = 1, int $column = 1)
    {
        $this->line   = $line;
        $this->column = $column;
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
}
