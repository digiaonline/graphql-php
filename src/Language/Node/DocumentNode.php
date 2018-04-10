<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;
use Digia\GraphQL\Util\SerializationInterface;

class DocumentNode extends AbstractNode implements NodeInterface
{
    /**
     * @var DefinitionNodeInterface[]
     */
    protected $definitions;

    /**
     * DocumentNode constructor.
     *
     * @param DefinitionNodeInterface[] $definitions
     * @param Location|null             $location
     */
    public function __construct(array $definitions, ?Location $location)
    {
        parent::__construct(NodeKindEnum::DOCUMENT, $location);

        $this->definitions = $definitions;
    }

    /**
     * @return DefinitionNodeInterface[]
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * @return array
     */
    public function getDefinitionsAsArray(): array
    {
        return \array_map(function (SerializationInterface $node) {
            return $node->toArray();
        }, $this->definitions);
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'        => $this->kind,
            'definitions' => $this->getDefinitionsAsArray(),
            'loc'         => $this->getLocationAsArray(),
        ];
    }

    /**
     * @param DefinitionNodeInterface[] $definitions
     * @return DocumentNode
     */
    protected function setDefinitions(array $definitions): DocumentNode
    {
        $this->definitions = $definitions;
        return $this;
    }
}
