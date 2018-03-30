<?php

namespace Digia\GraphQL\SchemaBuilder;

use Digia\GraphQL\Language\Node\SchemaDefinitionNode;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\TypeInterface;

interface BuilderContextInterface
{
    /**
     *
     */
    public function boot(): void;

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
