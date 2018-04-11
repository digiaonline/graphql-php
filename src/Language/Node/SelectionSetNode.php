<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;
use Digia\GraphQL\Util\SerializationInterface;

class SelectionSetNode extends AbstractNode implements NodeInterface
{
    /**
     * @var SelectionNodeInterface[]
     */
    protected $selections = [];

    /**
     * SelectionSetNode constructor.
     *
     * @param SelectionNodeInterface[] $selections
     * @param Location|null            $location
     */
    public function __construct(array $selections, ?Location $location)
    {
        parent::__construct(NodeKindEnum::SELECTION_SET, $location);

        $this->selections = $selections;
    }

    /**
     * @return SelectionNodeInterface[]
     */
    public function getSelections(): array
    {
        return $this->selections;
    }

    /**
     * @return array
     */
    public function getSelectionsAsArray(): array
    {
        return \array_map(function (SerializationInterface $node) {
            return $node->toArray();
        }, $this->selections);
    }

    /**
     * @param SelectionNodeInterface[] $selections
     * @return SelectionSetNode
     */
    public function setSelections(array $selections): SelectionSetNode
    {
        $this->selections = $selections;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'       => $this->kind,
            'loc'        => $this->getLocationAsArray(),
            'selections' => $this->getSelectionsAsArray(),
        ];
    }
}
