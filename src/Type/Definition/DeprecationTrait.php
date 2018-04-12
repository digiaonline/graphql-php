<?php

namespace Digia\GraphQL\Type\Definition;

trait DeprecationTrait
{
    /**
     * @var string|null
     */
    protected $deprecationReason;

    /**
     * @return null|string
     */
    public function getDeprecationReason(): ?string
    {
        return $this->deprecationReason;
    }

    /**
     * @return bool
     */
    public function isDeprecated(): bool
    {
        return null !== $this->deprecationReason;
    }

    /**
     * @param null|string $deprecationReason
     * @return $this
     */
    protected function setDeprecationReason(?string $deprecationReason)
    {
        $this->deprecationReason = $deprecationReason;
        return $this;
    }
}
