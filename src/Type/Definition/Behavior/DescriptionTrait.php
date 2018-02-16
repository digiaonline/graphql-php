<?php

namespace Digia\GraphQL\Type\Definition\Behavior;

trait DescriptionTrait
{

    /**
     * @var ?string
     */
    private $description;

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
