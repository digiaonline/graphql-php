<?php

namespace Digia\GraphQL\Type\Definition;

trait DeprecationTrait
{

    /**
     * @var string|null
     */
    protected $deprecationReason;

    /**
     * @var bool
     */
    protected $isDeprecated = false;

    /**
     * @return null|string
     */
    public function getDeprecationReason(): ?string
    {
        return $this->deprecationReason;
    }

    /**
     * @param null|string $deprecationReason
     *
     * @return $this
     */
    protected function setDeprecationReason(?string $deprecationReason)
    {
        if (null !== $deprecationReason) {
            $this->isDeprecated = true;
        }

        $this->deprecationReason = $deprecationReason;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDeprecated(): bool
    {
        return $this->isDeprecated;
    }
}
