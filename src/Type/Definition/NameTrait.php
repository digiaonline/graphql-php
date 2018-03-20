<?php

namespace Digia\GraphQL\Type\Definition;

trait NameTrait
{
    /**
     * @var string
     */
    protected $name;

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
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
