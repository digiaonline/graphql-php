<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Util\SerializationInterface;

trait FieldsTrait
{

    /**
     * @var FieldDefinitionNode[]
     */
    protected $fields;

    /**
     * @return bool
     */
    public function hasFields(): bool
    {
        return !empty($this->fields);
    }

    /**
     * @return FieldDefinitionNode[]
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
     * @param array|FieldDefinitionNode[] $fields
     * @return $this
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }
}
