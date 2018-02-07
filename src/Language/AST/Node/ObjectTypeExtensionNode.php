<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\KindEnum;

class ObjectTypeExtensionNode implements NodeInterface
{

    use KindTrait;

    /**
     * @inheritdoc
     */
    protected function configure(): array
    {
        return [
            'kind' => KindEnum::OBJECT_TYPE_EXTENSION,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        // TODO: Implement getValue() method.
    }
}
