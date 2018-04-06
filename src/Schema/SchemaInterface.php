<?php

namespace Digia\GraphQL\Schema;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Type\Definition\AbstractTypeInterface;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\TypeInterface;

interface SchemaInterface
{

    /**
     * @return TypeInterface|ObjectType|null
     */
    public function getQueryType(): ?TypeInterface;

    /**
     * @return TypeInterface|ObjectType|null
     */
    public function getMutationType(): ?TypeInterface;

    /**
     * @return TypeInterface|ObjectType|null
     */
    public function getSubscriptionType(): ?TypeInterface;

    /**
     * @param string $name
     *
     * @return Directive|null
     */
    public function getDirective(string $name): ?Directive;

    /**
     * @return Directive[]
     */
    public function getDirectives(): array;

    /**
     * @return NamedTypeInterface[]
     */
    public function getTypeMap(): array;

    /**
     * @return bool
     */
    public function getAssumeValid(): bool;

    /**
     * @param AbstractTypeInterface $abstractType
     * @param TypeInterface $possibleType
     *
     * @return bool
     */
    public function isPossibleType(
        AbstractTypeInterface $abstractType,
        TypeInterface $possibleType
    ): bool;

    /**
     * @param AbstractTypeInterface $abstractType
     *
     * @return null|TypeInterface[]
     */
    public function getPossibleTypes(AbstractTypeInterface $abstractType
    ): ?array;

    /**
     * @param string $name
     *
     * @return TypeInterface|null
     */
    public function getType(string $name): ?TypeInterface;

    /**
     * @return bool
     */
    public function hasAstNode(): bool;

    /**
     * @return NodeInterface
     */
    public function getAstNode(): ?NodeInterface;
}
