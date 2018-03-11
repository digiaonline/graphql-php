<?php

namespace Digia\GraphQL\Validation\Conflict;

class Conflict
{
    /**
     * @var string
     */
    protected $responseName;

    /**
     * @var mixed
     */
    protected $reason;

    /**
     * @var array
     */
    protected $fieldsA;

    /**
     * @var array
     */
    protected $fieldsB;

    /**
     * Conflict constructor.
     * @param string     $responseName
     * @param mixed     $reason
     * @param array|null $fieldsA
     * @param array|null $fieldsB
     */
    public function __construct(string $responseName, $reason, array $fieldsA, array $fieldsB)
    {
        $this->responseName = $responseName;
        $this->reason       = $reason;
        $this->fieldsA      = $fieldsA;
        $this->fieldsB      = $fieldsB;
    }

    /**
     * @return string
     */
    public function getResponseName(): string
    {
        return $this->responseName;
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @return array
     */
    public function getFieldsA(): array
    {
        return $this->fieldsA;
    }

    /**
     * @return array
     */
    public function getFieldsB(): array
    {
        return $this->fieldsB;
    }

    /**
     * @return array
     */
    public function getAllFields(): array
    {
        return array_merge($this->fieldsA, $this->fieldsB);
    }
}
