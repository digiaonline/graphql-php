<?php

namespace Digia\GraphQL\Type\Definition;

trait DescriptionTrait
{

    /**
     * @var ?string
     */
    protected $description;

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     */
    protected function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
