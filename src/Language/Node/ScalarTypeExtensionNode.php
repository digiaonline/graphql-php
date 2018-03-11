<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;

class ScalarTypeExtensionNode extends AbstractNode implements TypeExtensionNodeInterface
{

    use NameTrait;
    use DirectivesTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::SCALAR_TYPE_EXTENSION;

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'       => $this->kind,
            'name'       => $this->getNameAsArray(),
            'directives' => $this->getDirectivesAsArray(),
            'loc'        => $this->getLocationAsArray(),
        ];
    }
}
