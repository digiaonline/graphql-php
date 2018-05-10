<?php

namespace Digia\GraphQL\Execution;

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
     * @param Path|null    $previous
     * @param string|mixed $key
     */
    public function __construct(?Path $previous, $key)
    {
        $this->previous = $previous;
        $this->key      = $key;
    }

    /**
     * @return Path|null
     */
    public function getPrevious(): ?Path
    {
        return $this->previous;
    }

    /**
     * @return string|mixed
     */
    public function getKey()
    {
        return $this->key;
    }
}
