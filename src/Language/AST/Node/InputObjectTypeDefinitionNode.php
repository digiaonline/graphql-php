<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Behavior\ValueTrait;
use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Behavior\ConfigTrait;

class InputObjectTypeDefinitionNode implements NodeInterface
{

    use KindTrait;
    use ValueTrait;
    use ConfigTrait;

    /**
     * @inheritdoc
     */
    protected function beforeConfig(): void
    {
        $this->setKind(KindEnum::INPUT_OBJECT_TYPE_DEFINITION);
    }
}
