<?php

namespace Digia\GraphQL\Type\Definition;

use GraphQL\Contracts\TypeSystem\Common\DefaultValueAwareInterface;
use GraphQL\Contracts\TypeSystem\Common\NameAwareInterface;
use GraphQL\Contracts\TypeSystem\Common\TypeAwareInterface;
use GraphQL\Contracts\TypeSystem\DefinitionInterface;
use GraphQL\Contracts\TypeSystem\Type\TypeInterface;

interface InputValueInterface extends
    TypeAwareInterface,
    DefinitionInterface,
    NameAwareInterface,
    DefaultValueAwareInterface
{
    /**
     * @return TypeInterface|null
     */
    public function getNullableType(): ?TypeInterface;
}
