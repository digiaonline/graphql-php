<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Language\AST\Node\ArgumentNode;
use Digia\GraphQL\Type\Definition\LeafTypeInterface;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use function Digia\GraphQL\Util\arrayEvery;
use function Digia\GraphQL\Util\find;

function compareArguments($argumentsA, $argumentsB): bool
{
    if (\count($argumentsA) !== \count($argumentsB)) {
        return false;
    }

    return arrayEvery($argumentsA, function (ArgumentNode $argumentA) use ($argumentsB) {
        $argumentB = find($argumentsB, function (ArgumentNode $argument) use ($argumentA) {
            return $argument->getNameValue() === $argumentA->getNameValue();
        });

        if (null === $argumentB) {
            return false;
        }

        return compareValues($argumentA->getValue(), $argumentB->getValue());
    });
}

/**
 * @param $valueA
 * @param $valueB
 * @return bool
 */
function compareValues($valueA, $valueB): bool
{
    return $valueA == $valueB;
}

/**
 * Two types conflict if both types could not apply to a value simultaneously.
 * Composite types are ignored as their individual field types will be compared
 * later recursively. However List and Non-Null types must match.
 *
 * @param TypeInterface $typeA
 * @param TypeInterface $typeB
 * @return bool
 */
function compareTypes(TypeInterface $typeA, TypeInterface $typeB): bool
{
    if ($typeA instanceof ListType) {
        return $typeB instanceof ListType
            ? compareTypes($typeA->getOfType(), $typeB->getOfType())
            : true;
    }
    if ($typeB instanceof ListType) {
        return true;
    }
    if ($typeA instanceof NonNullType) {
        return $typeB instanceof NonNullType
            ? compareTypes($typeA->getOfType(), $typeB->getOfType())
            : true;
    }
    if ($typeB instanceof NonNullType) {
        return true;
    }
    if ($typeA instanceof LeafTypeInterface || $typeB instanceof LeafTypeInterface) {
        return $typeA !== $typeB;
    }
    return false;
}
