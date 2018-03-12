<?php

namespace Digia\GraphQL\Validation\Conflict;

class Reason
{
    /**
     * @var string
     */
    protected $responseName;

    /**
     * @var array|string
     */
    protected $reason;

    /**
     * Reason constructor.
     * @param string       $responseName
     * @param array|string $reason
     */
    public function __construct(string $responseName, $reason)
    {
        $this->responseName = $responseName;
        $this->reason       = $reason;
    }

    /**
     * @return string
     */
    public function getResponseName(): string
    {
        return $this->responseName;
    }

    /**
     * @return array|string
     */
    public function getReason()
    {
        return $this->reason;
    }
}
