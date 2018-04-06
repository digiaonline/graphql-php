<?php

namespace Digia\GraphQL\Language\Node;

class InputObjectTypeDefinitionNode extends AbstractNode implements TypeDefinitionNodeInterface,
    DirectivesAwareInterface, NameAwareInterface
{

    use DescriptionTrait;
    use NameTrait;
    use DirectivesTrait;
    use InputFieldsTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::INPUT_OBJECT_TYPE_DEFINITION;

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'description' => $this->getDescriptionAsArray(),
            'name' => $this->getNameAsArray(),
            'directives' => $this->getDirectivesAsArray(),
            'fields' => $this->getFieldsAsArray(),
            'loc' => $this->getLocationAsArray(),
        ];
    }
}
