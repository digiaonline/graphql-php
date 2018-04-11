<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class EnumValueDefinitionNode extends AbstractNode implements DefinitionNodeInterface, DirectivesAwareInterface,
    NameAwareInterface
{
    use DescriptionTrait;
    use NameTrait;
    use DirectivesTrait;

    /**
     * EnumValueDefinitionNode constructor.
     *
     * @param StringValueNode|null $description
     * @param NameNode             $name
     * @param DirectiveNode[]      $directives
     * @param Location|null        $location
     */
    public function __construct(
        ?StringValueNode $description,
        NameNode $name,
        array $directives,
        ?Location $location
    ) {
        parent::__construct(NodeKindEnum::ENUM_VALUE_DEFINITION, $location);

        $this->description = $description;
        $this->name        = $name;
        $this->directives  = $directives;
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
