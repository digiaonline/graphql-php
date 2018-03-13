<?php

namespace Digia\GraphQL\Language\Node;

class ObjectTypeExtensionNode extends AbstractNode implements TypeExtensionNodeInterface, DirectivesInterface
{
    use NameTrait;
    use InterfacesTrait;
    use DirectivesTrait;
    use FieldsTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::OBJECT_TYPE_EXTENSION;

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
