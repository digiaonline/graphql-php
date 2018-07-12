<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\ValueNodeInterface;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Util\TypeHelper;
use Digia\GraphQL\Util\ValueHelper;

/**
 * @param ArgumentNode[] $argumentsA
 * @param ArgumentNode[] $argumentsB
 * @return bool
 */
function compareArguments(array $argumentsA, array $argumentsB): bool
{
    return GraphQL::make(ValueHelper::class)->compareArguments($argumentsA, $argumentsB);
}

/**
 * @param ValueNodeInterface $valueA
 * @param ValueNodeInterface $valueB
 * @return bool
 */
function compareValues($valueA, $valueB): bool
{
    return GraphQL::make(ValueHelper::class)->compareValues($valueA, $valueB);
}

/**
 * @param TypeInterface $typeA
 * @param TypeInterface $typeB
 * @return bool
 */
function compareTypes(TypeInterface $typeA, TypeInterface $typeB): bool
{
    return GraphQL::make(TypeHelper::class)->compareTypes($typeA, $typeB);
}
