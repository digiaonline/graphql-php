<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\KindTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\LocationTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\ValueTrait;
use Digia\GraphQL\Language\AST\Node\Contract\ValueNodeInterface;
use Digia\GraphQL\ConfigObject;

class StringValueNode extends ConfigObject implements ValueNodeInterface
{

    use KindTrait;
    use LocationTrait;
    use ValueTrait;

    /**
     * @var string
     */
    protected $kind = KindEnum::STRING;

    /**
     * @var bool
     */
    protected $block;

    /**
     * @return bool
     */
    public function isBlock(): bool
    {
        return $this->block;
    }
}
