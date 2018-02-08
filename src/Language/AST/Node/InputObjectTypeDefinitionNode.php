<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Behavior\ConfigTrait;

class InputObjectTypeDefinitionNode implements NodeInterface
{

    use KindTrait;
    use ConfigTrait;

    /**
     * @inheritdoc
     */
    protected function configure(): array
    {
        return [
            'kind' => KindEnum::INPUT_OBJECT_TYPE_DEFINITION,
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
