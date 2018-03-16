<?php

namespace Digia\GraphQL\Language\Node;

class InputObjectTypeExtensionNode extends AbstractNode implements TypeExtensionNodeInterface,
    DirectivesAwareInterface, NameAwareInterface
{
    use NameTrait;
    use DirectivesTrait;
    use InputFieldsTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::INPUT_OBJECT_TYPE_EXTENSION;

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'       => $this->kind,
            'name'       => $this->getNameAsArray(),
            'directives' => $this->getDirectivesAsArray(),
            'fields'     => $this->getFieldsAsArray(),
            'loc'        => $this->getLocationAsArray(),
        ];
    }
}
