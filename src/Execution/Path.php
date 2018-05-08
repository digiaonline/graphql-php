<?php

namespace Digia\GraphQL\Execution;

/**
 * Class Path
 * @package Digia\GraphQL\Execution
 */
class Path
{
    /**
     * @var Path|null
     */
    protected $previous;

    /**
     * @var mixed
     */
    protected $key;

    /**
     * Path constructor.
     * @param Path|null $previous
     * @param mixed     $key
     */
    public function __construct(?Path $previous, $key)
    {
        $this->previous = $previous;
        $this->key      = $key;
    }

    /**
     * @return Path|null
     */
    public function getPrevious()
    {
        return $this->previous;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }
}
