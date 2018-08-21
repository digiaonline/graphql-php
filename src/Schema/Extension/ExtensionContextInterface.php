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
     * @return array
     */
    public function getExtendedOperationTypes(): array;

    /**
     * @return TypeInterface[]
     */
    public function getExtendedTypes(): array;

    /**
     * @return Directive[]
     */
    public function getExtendedDirectives(): array;
}
