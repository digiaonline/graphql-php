<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\KindTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\LocationTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\NameTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\ValueTrait;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\ConfigObject;

class ObjectFieldNode extends ConfigObject implements NodeInterface
{
    use KindTrait;
    use LocationTrait;
    use NameTrait;
    use ValueTrait;

    /**
     * @var string
     */
    protected $kind = KindEnum::OBJECT_FIELD;
}
