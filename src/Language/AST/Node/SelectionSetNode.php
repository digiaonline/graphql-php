<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Util\SerializationInterface;

class SelectionSetNode extends AbstractNode implements NodeInterface
{

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::SELECTION_SET;

    /**
     * @var SelectionNodeInterface[]
     */
    protected $selections;

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
        return array_map(function (SerializationInterface $node) {
            return $node->toArray();
        }, $this->selections);
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
