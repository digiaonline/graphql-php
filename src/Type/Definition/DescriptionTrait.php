<?php

namespace Digia\GraphQL\Type\Definition;

trait DescriptionTrait
{
    /**
     * @var string|null
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
     * @return $this
     */
    protected function setDescription(?string $description)
    {
        $this->description = $description;
        return $this;
    }
}
