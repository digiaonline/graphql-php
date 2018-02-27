<?php

namespace Digia\GraphQL\Type\Contract;

use Digia\GraphQL\Type\Definition\Contract\AbstractTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\TypeInterface;
use Digia\GraphQL\Type\Definition\ObjectType;

interface SchemaInterface
{
    /**
     * @return ObjectType
     */
    public function getQuery(): ObjectType;

    /**
     * @return ObjectType
     */
    public function getMutation(): ObjectType;

    /**
     * @return ObjectType
     */
    public function getSubscription(): ObjectType;

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
