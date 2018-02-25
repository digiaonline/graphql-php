<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

use Digia\GraphQL\Contract\SerializationInterface;
use Digia\GraphQL\Language\AST\Node\EnumValueDefinitionNode;

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
