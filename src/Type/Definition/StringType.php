<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Type\Exception\TypeErrorException;

class StringType extends AbstractScalarType
{

    /**
     * @inheritdoc
     */
    protected function configure(): array
    {
        return [
            'name'        => TypeEnum::STRING,
            'description' =>
                'The `String` scalar type represents textual data, represented as UTF-8 ' .
                'character sequences. The String type is most often used by GraphQL to ' .
                'represent free-form human-readable text.',
        ];
    }

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
    public function parseLiteral(NodeInterface $astNode, ...$args)
    {
        return $astNode->getKind() === KindEnum::STRING ? $astNode->getValue() : null;
    }

    /**
     * @param $value
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
