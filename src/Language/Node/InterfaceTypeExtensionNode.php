<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;

class InterfaceTypeExtensionNode extends AbstractNode implements TypeExtensionNodeInterface
{

    use NameTrait;
    use DirectivesTrait;
    use FieldsTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::INTERFACE_TYPE_EXTENSION;

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
