<?php

namespace Digia\GraphQL\Type\Definition;

use GraphQL\Contracts\TypeSystem\DefinitionInterface;

interface InputValueInterface extends DefinitionInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return TypeInterface|null
     */
    public function getType(): ?TypeInterface;

    /**
     * @return bool
     */
    public function hasDefaultValue(): bool;

    /**
     * @return null|mixed
     */
    public function getDefaultValue();
}
