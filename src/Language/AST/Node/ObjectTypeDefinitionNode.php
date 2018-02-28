<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\Node\DescriptionTrait;
use Digia\GraphQL\Language\AST\Node\DirectivesTrait;
use Digia\GraphQL\Language\AST\Node\FieldsTrait;
use Digia\GraphQL\Language\AST\Node\InterfacesTrait;
use Digia\GraphQL\Language\AST\Node\NameTrait;
use Digia\GraphQL\Language\AST\Node\TypeDefinitionNodeInterface;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class ObjectTypeDefinitionNode extends AbstractNode implements TypeDefinitionNodeInterface
{

    use DescriptionTrait;
    use NameTrait;
    use InterfacesTrait;
    use DirectivesTrait;
    use FieldsTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::OBJECT_TYPE_DEFINITION;

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'        => $this->kind,
            'description' => $this->getDescriptionAsArray(),
            'name'        => $this->getNameAsArray(),
            'interfaces'  => $this->getInterfacesAsArray(),
            'directives'  => $this->getDirectivesAsArray(),
            'fields'      => $this->getFieldsAsArray(),
            'loc'         => $this->getLocationAsArray(),
        ];
    }
}
