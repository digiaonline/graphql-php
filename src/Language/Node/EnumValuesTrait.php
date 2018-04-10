<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Util\SerializationInterface;

trait EnumValuesTrait
{
    /**
     * @var EnumValueDefinitionNode[]
     */
    protected $values;

    /**
     * @return bool
     */
    public function hasValues(): bool
    {
        return !empty($this->values);
    }

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
        return \array_map(function (SerializationInterface $node) {
            return $node->toArray();
        }, $this->values);
    }

    /**
     * @param array|EnumValueDefinitionNode[] $values
     * @return $this
     */
    public function setValues(array $values)
    {
        $this->values = $values;
        return $this;
    }
}
