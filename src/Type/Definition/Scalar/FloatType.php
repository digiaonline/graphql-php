<?php

namespace Digia\GraphQL\Type\Definition\Scalar;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Type\Definition\TypeEnum;

class FloatType extends ScalarType
{

    /**
     * @inheritdoc
     */
    protected function beforeConfig(): void
    {
        $this->setName(TypeEnum::FLOAT);
        $this->setDescription(
            'The `Float` scalar type represents signed double-precision fractional ' .
            'values as specified by ' .
            '[IEEE 754](http://en.wikipedia.org/wiki/IEEE_floating_point).'
        );
        $this->setSerialize(function ($value) {
            return $this->coerceValue($value);
        });
        $this->setParseValue(function ($value) {
            return $this->coerceValue($value);
        });
        $this->setParseLiteral(function (NodeInterface $astNode) {
            return in_array($astNode->getKind(), [KindEnum::FLOAT, KindEnum::INT]) ? $astNode->getValue() : null;
        });
    }

    /**
     * @param $value
     * @return float
     * @throws \TypeError
     */
    private function coerceValue($value): float
    {
        if ($value === '') {
            throw new \TypeError('Float cannot represent non numeric value: (empty string)');
        }

        if (is_numeric($value)) {
            return (float)$value;
        }

        throw new \TypeError('Float cannot represent non numeric value');
    }
}
