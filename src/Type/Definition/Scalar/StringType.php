<?php

namespace Digia\GraphQL\Type\Definition\Scalar;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Type\Definition\TypeEnum;
use Digia\GraphQL\Type\Exception\TypeErrorException;

class StringType extends ScalarType
{

    /**
     * @inheritdoc
     */
    protected function beforeConfig(): void
    {
        $this->setName(TypeEnum::STRING);
        $this->setDescription(
            'The `String` scalar type represents textual data, represented as UTF-8 ' .
            'character sequences. The String type is most often used by GraphQL to ' .
            'represent free-form human-readable text.'
        );
        $this->setSerialize(function ($value) {
            return $this->coerceValue($value);
        });
        $this->setParseValue(function ($value) {
            return $this->coerceValue($value);
        });
        $this->setParseLiteral(function (NodeInterface $astNode) {
            return $astNode->getKind() === KindEnum::STRING ? $astNode->getValue() : null;
        });
    }

    /**
     * @param $value
     * @return string
     * @throws \TypeError
     */
    private function coerceValue($value): string
    {
        if ($value === null) {
            return 'null';
        }

        if ($value === true) {
            return 'true';
        }

        if ($value === false) {
            return 'false';
        }

        if (!is_scalar($value)) {
            throw new \TypeError('String cannot represent a non-scalar value');
        }

        return (string)$value;
    }
}
