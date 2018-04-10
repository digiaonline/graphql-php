<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;
use Digia\GraphQL\Util\SerializationInterface;

class DirectiveDefinitionNode extends AbstractNode implements DefinitionNodeInterface, NameAwareInterface
{
    use DescriptionTrait;
    use NameTrait;
    use InputArgumentsTrait;

    /**
     * @var NameNode[]
     */
    protected $locations;

    /**
     * DirectiveDefinitionNode constructor.
     *
     * @param StringValueNode|null $description
     * @param NameNode             $name
     * @param ArgumentNode[]       $arguments
     * @param NameNode[]           $locations
     * @param Location|null        $location
     */
    public function __construct(
        ?StringValueNode $description,
        NameNode $name,
        array $arguments,
        array $locations,
        ?Location $location
    ) {
        parent::__construct(NodeKindEnum::DIRECTIVE_DEFINITION, $location);

        $this->description = $description;
        $this->name        = $name;
        $this->arguments   = $arguments;
        $this->locations   = $locations;
    }

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
        return \array_map(function (SerializationInterface $node) {
            return $node->toArray();
        }, $this->locations);
    }

    /**
     * @param NameNode[] $locations
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
