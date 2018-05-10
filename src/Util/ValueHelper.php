<?php

namespace Digia\GraphQL\Util;

use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Error\ResolutionException;
use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\EnumValueNode;
use Digia\GraphQL\Language\Node\ListValueNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NullValueNode;
use Digia\GraphQL\Language\Node\ObjectFieldNode;
use Digia\GraphQL\Language\Node\ObjectValueNode;
use Digia\GraphQL\Language\Node\ValueNodeInterface;
use Digia\GraphQL\Language\Node\VariableNode;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InputTypeInterface;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use function Digia\GraphQL\printNode;

class ValueHelper
{
    /**
     * @param array $argumentsA
     * @param array $argumentsB
     * @return bool
     */
    function compareArguments(array $argumentsA, array $argumentsB): bool
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

            return $this->compareValues($argumentA->getValue(), $argumentB->getValue());
        });
    }

    /**
     * @param ValueNodeInterface $valueA
     * @param ValueNodeInterface $valueB
     * @return bool
     */
    public function compareValues($valueA, $valueB): bool
    {
        return printNode($valueA) === printNode($valueB);
    }
}
