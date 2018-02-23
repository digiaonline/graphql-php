<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Contract\SerializationInterface;
use Digia\GraphQL\Language\AST\Node\Contract\DefinitionNodeInterface;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\NodeKindEnum;

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
     * @return array
     */
    public function getDefinitionsAsArray(): array
    {
        return array_map(function (SerializationInterface $node) {
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
            'loc'         => $this->getLocationAsArray(),
            'definitions' => $this->getDefinitionsAsArray(),
        ];
    }
}
