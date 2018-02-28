<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\DirectivesTrait;
use Digia\GraphQL\Language\AST\Node\FieldsTrait;
use Digia\GraphQL\Language\AST\Node\NameTrait;
use Digia\GraphQL\Language\AST\Node\TypeExtensionNodeInterface;

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
            'kind'        => $this->kind,
            'name'        => $this->getNameAsArray(),
            'directives'  => $this->getDirectivesAsArray(),
            'fields'      => $this->getFieldsAsArray(),
            'loc'         => $this->getLocationAsArray(),
        ];
    }
}
