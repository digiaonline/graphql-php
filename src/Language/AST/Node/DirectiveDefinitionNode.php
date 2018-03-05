<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Util\SerializationInterface;

class DirectiveDefinitionNode extends AbstractNode implements DefinitionNodeInterface
{

    use DescriptionTrait;
    use NameTrait;
    use InputArgumentsTrait;

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
     * @param array|NameNode[] $locations
     * @return $this
     */
    public function setLocations(array $locations)
    {
        $this->locations = $locations;
        return $this;
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
