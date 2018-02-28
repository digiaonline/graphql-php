<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;

class ObjectValueNode extends AbstractNode implements ValueNodeInterface
{

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::OBJECT;

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
