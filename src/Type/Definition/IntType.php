<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\AST\ASTNodeInterface;
use Digia\GraphQL\Language\AST\KindEnum;

class IntType extends AbstractScalarType
{

    const MAX_INT = 2147483647;
    const MIN_INT = -2147483648;

    /**
     * @var string
     */
    protected $name = TypeEnum::INT;

    /**
     * @var string
     */
    protected $description =
        'The `Int` scalar type represents non-fractional signed whole numeric ' .
        'values. Int can represent values between -(2^31) and 2^31 - 1.';

    /**
     * @inheritdoc
     */
    public function serialize($value)
    {
        return $this->coerceValue($value);
    }

    /**
     * @inheritdoc
     */
    public function parseValue($value)
    {
        return $this->coerceValue($value);
    }

    /**
     * @inheritdoc
     */
    public function parseLiteral(ASTNodeInterface $astNode, ...$args)
    {
        return $astNode->getKind() === KindEnum::INT ? $astNode->getValue() : null;
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
