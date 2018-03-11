<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Util\SerializationInterface;

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

    /**
     * @return array
     */
    public function getFieldsAsArray(): array
    {
        return array_map(function (SerializationInterface $node) {
            return $node->toArray();
        }, $this->fields);
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }
}
