<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class DocumentNode extends AbstractNode
{
    /**
     * @var DefinitionNodeInterface[]|TypeSystemExtensionNodeInterface[]
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
     * @return DefinitionNodeInterface[]|TypeSystemExtensionNodeInterface[]
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * @return array
     */
    public function getDefinitionsAST(): array
    {
        return \array_map(function (NodeInterface $node) {
            return $node->toAST();
        }, $this->definitions);
    }

    /**
     * @inheritdoc
     */
    public function toAST(): array
    {
        return [
            'kind'        => $this->kind,
            'definitions' => $this->getDefinitionsAST(),
            'loc'         => $this->getLocationAST(),
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
