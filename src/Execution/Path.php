<?php

namespace Digia\GraphQL\Execution;

class Path
{
    private $previous;

    private $key;

    /**
     * Path constructor.
     * @param $previous
     * @param $key
     */
    public function __construct($previous, $key)
    {
        $this->previous = $previous;
        $this->key      = $key;
    }

    /**
     * @return mixed
     */
    public function getPrevious()
    {
        return $this->previous;
    }

    /**
     * @param mixed $previous
     * @return Path
     */
    public function setPrevious($previous)
    {
        $this->previous = $previous;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     * @return Path
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }
}