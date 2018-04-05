<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Util\SerializationInterface;

class ListValueNode extends AbstractNode implements ValueNodeInterface
{

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::LIST;

    /**
     * @var array|ValueNodeInterface[]
     */
    protected $values;

    /**
     * @return array|ValueNodeInterface[]
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

    /**
     * @param array|ValueNodeInterface[] $values
     * @return $this
     */
    public function setValues(array $values)
    {
        $this->values = $values;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'   => $this->kind,
            'loc'    => $this->getLocationAsArray(),
            'values' => $this->getValuesAsArray(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return json_encode(array_map(function(ValueNodeInterface $node) {
            return $node->getValue();
        }, $this->getValues()));
    }
}
