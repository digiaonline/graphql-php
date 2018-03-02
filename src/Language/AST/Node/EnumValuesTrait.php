<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Util\SerializationInterface;

trait EnumValuesTrait
{

    /**
     * @var EnumValueDefinitionNode[]
     */
    protected $values;

    /**
     * @return EnumValueDefinitionNode[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @return array
     */
    public function getValuesAsArray(): array
    {
        return array_map(function (SerializationInterface $node) {
            return $node->toArray();
        }, $this->values);
    }
}
