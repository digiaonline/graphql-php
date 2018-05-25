<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class InterfaceTypeDefinitionNode extends AbstractNode implements TypeDefinitionNodeInterface,
    DirectivesAwareInterface, NameAwareInterface
{
    use DescriptionTrait;
    use NameTrait;
    use DirectivesTrait;
    use FieldsTrait;

    /**
     * InterfaceTypeDefinitionNode constructor.
     *
     * @param StringValueNode|null  $description
     * @param NameNode              $name
     * @param DirectiveNode[]       $directives
     * @param FieldDefinitionNode[] $fields
     * @param Location|null         $location
     */
    public function __construct(
        ?StringValueNode $description,
        NameNode $name,
        array $directives,
        array $fields,
        ?Location $location
    ) {
        parent::__construct(NodeKindEnum::INTERFACE_TYPE_DEFINITION, $location);

        $this->description = $description;
        $this->name        = $name;
        $this->directives  = $directives;
        $this->fields      = $fields;
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
            'fields'      => $this->getFieldsAsArray(),
            'loc'         => $this->getLocationAsArray(),
        ];
    }
}
