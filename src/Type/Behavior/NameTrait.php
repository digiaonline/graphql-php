<?php

namespace Digia\GraphQL\Type\Behavior;

trait NameTrait
{

    // TODO: Add support for automatically resolving the name from the class name

    /**
     * @var ?string
     */
    private $name;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    protected function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName();
    }
}
