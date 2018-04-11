<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;
use Digia\GraphQL\Util\SerializationInterface;

class ObjectValueNode extends AbstractNode implements ValueNodeInterface
{
    /**
     * @var ObjectFieldNode[]
     */
    protected $fields;

    /**
     * ObjectValueNode constructor.
     *
     * @param ObjectFieldNode[] $fields
     * @param Location|null     $location
     */
    public function __construct(array $fields, ?Location $location)
    {
        parent::__construct(NodeKindEnum::OBJECT, $location);

        $this->fields = $fields;
    }

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
        return \array_map(function (SerializationInterface $node) {
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
