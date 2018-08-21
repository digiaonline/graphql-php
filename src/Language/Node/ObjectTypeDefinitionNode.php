<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class ObjectTypeDefinitionNode extends AbstractNode implements TypeSystemDefinitionNodeInterface, DirectivesAwareInterface,
    NameAwareInterface, NamedTypeNodeInterface
{
    use DescriptionTrait;
    use NameTrait;
    use InterfacesTrait;
    use DirectivesTrait;
    use FieldsTrait;

    /**
     * ObjectTypeDefinitionNode constructor.
     *
     * @param StringValueNode|null  $description
     * @param NameNode              $name
     * @param NamedTypeNode[]       $interfaces
     * @param DirectiveNode[]       $directives
     * @param FieldDefinitionNode[] $fields
     * @param Location|null         $location
     */
    public function __construct(
        ?StringValueNode $description,
        NameNode $name,
        array $interfaces,
        array $directives,
        array $fields,
        ?Location $location
    ) {
        parent::__construct(NodeKindEnum::OBJECT_TYPE_DEFINITION, $location);

        $this->description = $description;
        $this->name        = $name;
        $this->interfaces  = $interfaces;
        $this->directives  = $directives;
        $this->fields      = $fields;
    }

    /**
     * @inheritdoc
     */
    public function toAST(): array
    {
        return [
            'kind'        => $this->kind,
            'description' => $this->getDescriptionAST(),
            'name'        => $this->getNameAST(),
            'interfaces'  => $this->getInterfacesAST(),
            'directives'  => $this->getDirectivesAST(),
            'fields'      => $this->getFieldsAST(),
            'loc'         => $this->getLocationAST(),
        ];
    }
}
