<?php

namespace Digia\GraphQL\Language\Node;

class ObjectTypeDefinitionNode extends AbstractNode implements TypeDefinitionNodeInterface, DirectivesAwareInterface
{

    use DescriptionTrait;
    use NameTrait;
    use InterfacesTrait;
    use DirectivesTrait;
    use FieldsTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::OBJECT_TYPE_DEFINITION;

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'description' => $this->getDescriptionAsArray(),
            'name' => $this->getNameAsArray(),
            'interfaces' => $this->getInterfacesAsArray(),
            'directives' => $this->getDirectivesAsArray(),
            'fields' => $this->getFieldsAsArray(),
            'loc' => $this->getLocationAsArray(),
        ];
    }
}
