<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;

class EnumValueDefinitionNode extends AbstractNode implements DefinitionNodeInterface
{

    use DescriptionTrait;
    use NameTrait;
    use DirectivesTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::ENUM_VALUE_DEFINITION;

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
