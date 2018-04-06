<?php

namespace Digia\GraphQL\Schema\Extension;

use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\TypeInterface;

interface ExtensionContextInterface
{

    /**
     * @return bool
     */
    public function isSchemaExtended(): bool;

    /**
     * @return TypeInterface|null
     */
    public function getExtendedQueryType(): ?TypeInterface;

    /**
     * @return TypeInterface|null
     */
    public function getExtendedMutationType(): ?TypeInterface;

    /**
     * @return TypeInterface|null
     */
    public function getExtendedSubscriptionType(): ?TypeInterface;

    /**
     * @return TypeInterface[]
     */
    public function getExtendedTypes(): array;

    /**
     * @return Directive[]
     */
    public function getExtendedDirectives(): array;
}
