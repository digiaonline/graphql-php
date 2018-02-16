<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\KindTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\LocationTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\TypeTrait;
use Digia\GraphQL\Language\AST\Node\Contract\TypeNodeInterface;
use Digia\GraphQL\ConfigObject;

class NonNullTypeNode extends ConfigObject implements TypeNodeInterface
{

    use KindTrait;
    use LocationTrait;
    use TypeTrait;

    /**
     * @var string
     */
    protected $kind = KindEnum::NON_NULL_TYPE;
}
