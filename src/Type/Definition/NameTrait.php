<?php

namespace Digia\GraphQL\Type\Definition;

trait NameTrait
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
