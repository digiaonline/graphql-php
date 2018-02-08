<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Behavior\ConfigTrait;
use Digia\GraphQL\Behavior\ValueTrait;
use Digia\GraphQL\Language\AST\KindEnum;

class SchemaDefinitionNode implements NodeInterface
{

    use KindTrait;
    use ValueTrait;
    use ConfigTrait;

    /**
     * @inheritdoc
     */
    protected function beforeConfig(): void
    {
        $this->setKind(KindEnum::SCHEMA_DEFINITION);
    }
}
