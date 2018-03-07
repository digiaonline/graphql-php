<?php

namespace Digia\GraphQL\Execution;

class ResponsePath
{
    /**
     * @var ResponsePath|null
     */
    protected $prev;

    /**
     * @var string|int
     */
    protected $key;

    /**
     * ResponsePath constructor.
     *
     * @param ResponsePath|null $prev
     * @param int|string        $key
     */
    public function __construct(?ResponsePath $prev, $key)
    {
        $this->prev = $prev;
        $this->key  = $key;
    }

    /**
     * @return ResponsePath|null
     */
    public function getPrev(): ?ResponsePath
    {
        return $this->prev;
    }

    /**
     * @return int|string
     */
    public function getKey()
    {
        return $this->key;
    }
}
