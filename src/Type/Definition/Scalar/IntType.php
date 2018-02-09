<?php

namespace Digia\GraphQL\Type\Definition\Scalar;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Type\Definition\TypeEnum;

class IntType extends ScalarType
{

    const MAX_INT = 2147483647;
    const MIN_INT = -2147483648;

    /**
     * @inheritdoc
     */
    protected function beforeConfig(): void
    {
        $this->setName(TypeEnum::INT);
        $this->setDescription(
            'The `Int` scalar type represents non-fractional signed whole numeric ' .
            'values. Int can represent values between -(2^31) and 2^31 - 1.'
        );
        $this->setSerialize(function ($value) {
            return $this->coerceValue($value);
        });
        $this->setParseValue(function ($value) {
            return $this->coerceValue($value);
        });
        $this->setParseLiteral(function (NodeInterface $astNode) {
            return $astNode->getKind() === KindEnum::INT ? $astNode->getValue() : null;
        });
    }

    /**
     * @param $value
     * @return int
     * @throws \TypeError
     */
    private function coerceValue($value)
    {
        if ($value === '') {
            throw new \TypeError('Int cannot represent non 32-bit signed integer value: (empty string)');
        }

        $intValue = (int)$value;

        if (!is_int($intValue) || $value > self::MAX_INT || $value < self::MIN_INT) {
            throw new \TypeError('Int cannot represent non 32-bit signed integer value');
        }

        $floatValue = (float)$value;

        if ($floatValue != $intValue || floor($floatValue) !== $floatValue) {
            throw new \TypeError('Int cannot represent non-integer value');
        }

        return $intValue;
    }
}
