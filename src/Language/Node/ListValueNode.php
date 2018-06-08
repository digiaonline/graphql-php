<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class ListValueNode extends AbstractNode implements ValueNodeInterface
{
    /**
     * @var ValueNodeInterface[]
     */
    protected $values;

    /**
     * ListValueNode constructor.
     *
     * @param ValueNodeInterface[] $values
     * @param Location|null        $location
     */
    public function __construct(array $values, ?Location $location)
    {
        parent::__construct(NodeKindEnum::LIST, $location);

        $this->values = $values;
    }

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
    public function getValuesAST(): array
    {
        return \array_map(function (ValueNodeInterface $node) {
            return $node->toAST();
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
    public function toAST(): array
    {
        return [
            'kind'   => $this->kind,
            'loc'    => $this->getLocationAST(),
            'values' => $this->getValuesAST(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return \json_encode(\array_map(function (ValueAwareInterface $node) {
            return $node->getValue();
        }, $this->getValues()));
    }
}
