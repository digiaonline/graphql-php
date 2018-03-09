<?php

namespace Digia\GraphQL\Type;

use Digia\GraphQL\Type\Definition\AbstractTypeInterface;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\TypeInterface;

interface SchemaInterface
{

    /**
     * @return ObjectType
     */
    public function getQuery(): ObjectType;

    /**
     * @return ObjectType|null
     */
    public function getMutation(): ?ObjectType;

    /**
     * @return ObjectType|null
     */
    public function getSubscription(): ?ObjectType;

    /**
     * @param string $name
     * @return Directive|null
     */
    public function getDirective(string $name): ?Directive;

    /**
     * @return array
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
     * @param TypeInterface         $possibleType
     * @return bool
     */
    public function isPossibleType(AbstractTypeInterface $abstractType, TypeInterface $possibleType): bool;

    /**
     * @param AbstractTypeInterface $abstractType
     * @return null|TypeInterface[]
     */
    public function getPossibleTypes(AbstractTypeInterface $abstractType): ?array;

    /**
     * @param string $name
     * @return TypeInterface|null
     */
    public function getType(string $name): ?TypeInterface;
}
