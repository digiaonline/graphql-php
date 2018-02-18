<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\ConfigObject;
use function Digia\GraphQL\Util\invariant;

/**
 * A representation of source input to GraphQL.
 * `name` and `locationOffset` are optional. They are useful for clients who
 * store GraphQL documents in source files; for example, if the GraphQL input
 * starts at line 40 in a file named Foo.graphql, it might be useful for name to
 * be "Foo.graphql", line to be 40 and column to be 0.
 * line and column are 1-indexed
 */

class Source
{

    /**
     * @var string
     */
    private $body;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $line;

    /**
     * @var int
     */
    private $column;

    /**
     * Source constructor.
     *
     * @param string      $body
     * @param null|string $name
     * @param int|null    $line
     * @param int|null    $column
     */
    public function __construct(string $body, ?string $name = 'GraphQL request', ?int $line = 1, ?int $column = 1)
    {
        $this->body   = $body;
        $this->name   = $name;
        $this->line   = $line;
        $this->column = $column;
    }

    /**
     * @return int
     */
    public function getBodyLength(): int
    {
        return mb_strlen($this->body);
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
     * @param string $body
     * @return Source
     */
    protected function setBody(string $body): Source
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @param string $name
     * @return Source
     */
    protected function setName(string $name): Source
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param int $line
     * @return Source
     * @throws \Exception
     */
    protected function setLine(int $line): Source
    {
        invariant($line > 0, 'line is 1-indexed and must be positive');

        $this->line = $line;
        return $this;
    }

    /**
     * @param int $column
     * @return Source
     * @throws \Exception
     */
    protected function setColumn(int $column): Source
    {
        invariant($column > 0, 'column is 1-indexed and must be positive');

        $this->column = $column;
        return $this;
    }
}
