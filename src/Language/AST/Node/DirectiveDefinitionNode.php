<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Contract\SerializationInterface;
use Digia\GraphQL\Language\AST\Node\Behavior\ArgumentsTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\DescriptionTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\NameTrait;
use Digia\GraphQL\Language\AST\Node\Contract\DefinitionNodeInterface;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class DirectiveDefinitionNode extends AbstractNode implements DefinitionNodeInterface
{

    use DescriptionTrait;
    use NameTrait;
    use ArgumentsTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::DIRECTIVE_DEFINITION;

    /**
     * @var NameNode[]
     */
    protected $locations;

    /**
     * @return NameNode[]
     */
    public function getLocations(): array
    {
        return $this->locations;
    }

    /**
     * @return array
     */
    public function getLocationsAsArray(): array
    {
        return array_map(function (SerializationInterface $node) {
            return $node->toArray();
        }, $this->locations);
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'        => $this->kind,
            'description' => $this->getDescriptionAsArray(),
            'name'        => $this->getNameAsArray(),
            'arguments'   => $this->getArgumentsAsArray(),
            'locations'   => $this->getLocationsAsArray(),
            'loc'         => $this->getLocationAsArray(),
        ];
    }
}
