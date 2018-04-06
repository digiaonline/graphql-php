<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Util\SerializationInterface;

class DocumentNode extends AbstractNode implements NodeInterface
{

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::DOCUMENT;

    /**
     * @var array|DefinitionNodeInterface[]
     */
    protected $definitions;

    /**
     * @return array|DefinitionNodeInterface[]
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * @param array|DefinitionNodeInterface[] $definitions
     *
     * @return DocumentNode
     */
    public function setDefinitions($definitions)
    {
        $this->definitions = $definitions;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'definitions' => $this->getDefinitionsAsArray(),
            'loc' => $this->getLocationAsArray(),
        ];
    }

    /**
     * @return array
     */
    public function getDefinitionsAsArray(): array
    {
        return array_map(function (SerializationInterface $node) {
            return $node->toArray();
        }, $this->definitions);
    }
}
