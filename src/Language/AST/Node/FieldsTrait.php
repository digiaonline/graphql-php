<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\SerializationInterface;
use Digia\GraphQL\Language\AST\Node\FieldDefinitionNode;

trait FieldsTrait
{

    /**
     * @var FieldDefinitionNode[]
     */
    protected $fields;

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
}
