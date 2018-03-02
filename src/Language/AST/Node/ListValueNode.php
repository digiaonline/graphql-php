<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
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
}
