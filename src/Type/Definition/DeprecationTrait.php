<?php

namespace Digia\GraphQL\Type\Definition;

trait DeprecationTrait
{

    /**
     * @var ?string
     */
    private $deprecationReason;

    /**
     * @var bool
     */
    private $isDeprecated = false;

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
        return $this->isDeprecated;
    }

    /**
     * @param null|string $deprecationReason
     * @return $this
     */
    protected function setDeprecationReason(?string $deprecationReason)
    {
        if ($deprecationReason) {
            $this->isDeprecated = true;
        }

        $this->deprecationReason = $deprecationReason;

        return $this;
    }

    /**
     * @param bool $isDeprecated
     * @throws \TypeError
     * @noinspection PhpUnusedParameterInspection
     */
    public function setIsDeprecated(bool $isDeprecated): void
    {
        throw new \TypeError('You should provide "deprecationReason" instead of "isDeprecated".');
    }
}
