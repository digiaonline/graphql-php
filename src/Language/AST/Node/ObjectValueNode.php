<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\KindTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\LocationTrait;
use Digia\GraphQL\Language\AST\Node\Contract\ValueNodeInterface;
use Digia\GraphQL\ConfigObject;

class ObjectValueNode extends ConfigObject implements ValueNodeInterface
{

    use KindTrait;
    use LocationTrait;

    /**
     * @var string
     */
    protected $kind = KindEnum::OBJECT;

    /**
     * @var ObjectFieldNode[]
     */
    protected $fields;

    /**
     * @return ObjectFieldNode[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
