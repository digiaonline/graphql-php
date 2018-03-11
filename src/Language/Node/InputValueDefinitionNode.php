<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;

class InputValueDefinitionNode extends AbstractNode implements DefinitionNodeInterface
{

    use DescriptionTrait;
    use NameTrait;
    use TypeTrait;
    use DefaultValueTrait;
    use DirectivesTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::INPUT_VALUE_DEFINITION;

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'         => $this->kind,
            'description'  => $this->getDescriptionAsArray(),
            'name'         => $this->getNameAsArray(),
            'type'         => $this->getTypeAsArray(),
            'defaultValue' => $this->getDefaultValueAsArray(),
            'directives'   => $this->getDirectivesAsArray(),
            'loc'          => $this->getLocationAsArray(),
        ];
    }
}
