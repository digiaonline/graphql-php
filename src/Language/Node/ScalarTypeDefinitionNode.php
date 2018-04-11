<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class ScalarTypeDefinitionNode extends AbstractNode implements TypeDefinitionNodeInterface, DirectivesAwareInterface,
    NameAwareInterface
{
    use DescriptionTrait;
    use NameTrait;
    use DirectivesTrait;

    /**
     * ScalarTypeDefinitionNode constructor.
     *
     * @param StringValueNode|null $description
     * @param NameNode             $name
     * @param DirectiveNode[]      $directives
     * @param Location|null        $location
     */
    public function __construct(?StringValueNode $description, NameNode $name, array $directives, ?Location $location)
    {
        parent::__construct(NodeKindEnum::SCALAR_TYPE_DEFINITION, $location);

        $this->directives  = $directives;
        $this->description = $description;
        $this->name        = $name;
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
            'directives'  => $this->getDirectivesAsArray(),
            'loc'         => $this->getLocationAsArray(),
        ];
    }
}
