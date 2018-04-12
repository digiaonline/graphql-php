<?php

namespace Digia\GraphQL\Type\Definition;

trait NameTrait
{
    /**
     * @var string|null
     */
    protected $name;

    /**
     * Name can be null for `LIST` and `NON_NULL`.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
