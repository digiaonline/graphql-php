<?php

namespace Digia\GraphQL\Language\Node;

class ScalarTypeDefinitionNode extends AbstractNode implements TypeDefinitionNodeInterface, DirectivesAwareInterface,
    NameAwareInterface
{

    use DescriptionTrait;
    use NameTrait;
    use DirectivesTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::SCALAR_TYPE_DEFINITION;

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
            'loc' => $this->getLocationAsArray(),
        ];
    }
}
