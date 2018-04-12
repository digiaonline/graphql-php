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
}
