<?php

namespace Digia\GraphQL\Type\Definition;

trait DescriptionTrait
{
    /**
     * @var string|null
     */
    protected $description;

    /**
     * @return bool
     */
    public function hasDescription(): bool
    {
        return null !== $this->description;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }
}
