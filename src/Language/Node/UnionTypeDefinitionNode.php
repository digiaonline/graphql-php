<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;

class UnionTypeDefinitionNode extends AbstractNode implements TypeDefinitionNodeInterface
{

    use DescriptionTrait;
    use NameTrait;
    use DirectivesTrait;
    use TypesTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::UNION_TYPE_DEFINITION;

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
            'types'       => $this->getTypesAsArray(),
            'loc'         => $this->getLocationAsArray(),
        ];
    }
}
