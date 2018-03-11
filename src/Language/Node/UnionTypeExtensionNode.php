<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;

class UnionTypeExtensionNode extends AbstractNode implements TypeExtensionNodeInterface
{

    use NameTrait;
    use DirectivesTrait;
    use TypesTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::UNION_TYPE_EXTENSION;

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'       => $this->kind,
            'name'       => $this->getNameAsArray(),
            'directives' => $this->getDirectivesAsArray(),
            'types'      => $this->getTypesAsArray(),
            'loc'        => $this->getLocationAsArray(),
        ];
    }
}
