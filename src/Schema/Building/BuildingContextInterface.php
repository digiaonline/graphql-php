<?php

namespace Digia\GraphQL\Schema\Building;

use Digia\GraphQL\Language\Node\SchemaDefinitionNode;
use Digia\GraphQL\Type\Definition\Directive;
use GraphQL\Contracts\TypeSystem\Type\TypeInterface;

interface BuildingContextInterface
{
    /**
     * @return TypeInterface|null
     */
    public function buildQueryType(): ?TypeInterface;

    /**
     * @return TypeInterface|null
     */
    public function buildMutationType(): ?TypeInterface;

    /**
     * @return TypeInterface|null
     */
    public function buildSubscriptionType(): ?TypeInterface;

    /**
     * @return TypeInterface[]
     */
    public function buildTypes(): array;

    /**
     * @return Directive[]
     */
    public function buildDirectives(): array;

    /**
     * @return SchemaDefinitionNode|null
     */
    public function getSchemaDefinition(): ?SchemaDefinitionNode;
}
