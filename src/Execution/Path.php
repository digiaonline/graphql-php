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
     * @var string|mixed
     */
    protected $key;

    /**
     * Path constructor.
     * @param Path|null $previous
     * @param           $key
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
     * @return mixed|string
     */
    public function getKey()
    {
        return $this->key;
    }
}