<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class ObjectTypeExtensionNode extends AbstractNode implements TypeExtensionNodeInterface, DirectivesAwareInterface,
    NameAwareInterface
{
    use NameTrait;
    use InterfacesTrait;
    use DirectivesTrait;
    use FieldsTrait;

    /**
     * ObjectTypeExtensionNode constructor.
     *
     * @param NameNode              $name
     * @param NamedTypeNode[]       $interfaces
     * @param DirectiveNode[]       $directives
     * @param FieldDefinitionNode[] $fields
     * @param Location|null         $location
     */
    public function __construct(
        NameNode $name,
        array $interfaces,
        array $directives,
        array $fields,
        ?Location $location
    ) {
        parent::__construct(NodeKindEnum::OBJECT_TYPE_EXTENSION, $location);

        $this->name        = $name;
        $this->interfaces  = $interfaces;
        $this->directives  = $directives;
        $this->fields      = $fields;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'       => $this->kind,
            'name'       => $this->getNameAsArray(),
            'interfaces' => $this->getInterfacesAsArray(),
            'directives' => $this->getDirectivesAsArray(),
            'fields'     => $this->getFieldsAsArray(),
            'loc'        => $this->getLocationAsArray(),
        ];
    }
}
