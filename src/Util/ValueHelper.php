<?php

namespace Digia\GraphQL\Util;

use Digia\GraphQL\Language\Node\ArgumentNode;
use function Digia\GraphQL\printNode;

class ValueHelper
{
    /**
     * @param ArgumentNode[] $argumentsA
     * @param ArgumentNode[] $argumentsB
     * @return bool
     */
    public static function compareArguments(array $argumentsA, array $argumentsB): bool
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

            return printNode($argumentA->getValue()) === printNode($argumentB->getValue());
        });
    }
}
