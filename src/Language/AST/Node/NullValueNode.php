<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\KindTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\LocationTrait;
use Digia\GraphQL\Language\AST\Node\Contract\ValueNodeInterface;
use Digia\GraphQL\ConfigObject;

class NullValueNode extends ConfigObject implements ValueNodeInterface
{

    use KindTrait;
    use LocationTrait;

    /**
     * @var string
     */
    protected $kind = KindEnum::NULL;
}
