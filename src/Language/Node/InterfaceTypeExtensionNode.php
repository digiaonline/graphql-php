<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class InterfaceTypeExtensionNode extends AbstractNode implements
    TypeSystemExtensionNodeInterface,
    NameAwareInterface,
    DirectivesAwareInterface
{
    use NameTrait;
    use DirectivesTrait;
    use FieldsTrait;

    /**
     * InterfaceTypeExtensionNode constructor.
     *
     * @param NameNode              $name
     * @param DirectiveNode[]       $directives
     * @param FieldDefinitionNode[] $fields
     * @param Location|null         $location
     */
    public function __construct(NameNode $name, array $directives, array $fields, ?Location $location)
    {
        parent::__construct(NodeKindEnum::INTERFACE_TYPE_EXTENSION, $location);

        $this->name       = $name;
        $this->directives = $directives;
        $this->fields     = $fields;
    }

    /**
     * @inheritdoc
     */
    public function toAST(): array
    {
        return [
            'kind'       => $this->kind,
            'name'       => $this->getNameAST(),
            'directives' => $this->getDirectivesAST(),
            'fields'     => $this->getFieldsAST(),
            'loc'        => $this->getLocationAST(),
        ];
    }
}
