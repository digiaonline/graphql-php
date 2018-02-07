<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\KindEnum;

class BooleanType extends AbstractScalarType
{

    /**
     * @inheritdoc
     */
    protected function configure(): array
    {
        return [
            'name'        => TypeEnum::BOOLEAN,
            'description' => 'The `Boolean` scalar type represents `true` or `false`.',
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
        return $astNode->getKind() === KindEnum::BOOLEAN ? $astNode->getValue() : null;
    }

    /**
     * @param $value
     * @return bool
     * @throws \TypeError
     */
    private function coerceValue($value): bool
    {
        if ($value === '') {
            throw new \TypeError('Boolean cannot represent non boolean value: (empty string)');
        }

        if (!is_scalar($value)) {
            throw new \TypeError('Boolean cannot represent a non-scalar value');
        }

        return (bool)$value;
    }
}
