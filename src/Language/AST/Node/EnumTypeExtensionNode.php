<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;

class EnumTypeExtensionNode extends AbstractNode implements TypeExtensionNodeInterface
{

    use NameTrait;
    use DirectivesTrait;
    use EnumValuesTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::ENUM_TYPE_EXTENSION;

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'       => $this->kind,
            'name'       => $this->getNameAsArray(),
            'directives' => $this->getDirectivesAsArray(),
            'values'     => $this->getValuesAsArray(),
            'loc'        => $this->getLocationAsArray(),
        ];
    }
}
