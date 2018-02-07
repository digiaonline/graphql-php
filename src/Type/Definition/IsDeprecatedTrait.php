<?php

namespace Digia\GraphQL\Type\Definition;

trait IsDeprecatedTrait
{

    /**
     * @var bool
     */
    private $isDeprecated = false;

    /**
     * @return bool
     */
    public function isDeprecated(): bool
    {
        return $this->isDeprecated;
    }

    /**
     * @param bool $isDeprecated
     */
    protected function setIsDeprecated(bool $isDeprecated): void
    {
        $this->isDeprecated = $isDeprecated;
    }
}
