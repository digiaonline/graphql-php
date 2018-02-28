<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\DescriptionTrait;
use Digia\GraphQL\Language\AST\Node\DirectivesTrait;
use Digia\GraphQL\Language\AST\Node\InputFieldsTrait;
use Digia\GraphQL\Language\AST\Node\NameTrait;
use Digia\GraphQL\Language\AST\Node\DefinitionNodeInterface;

class InputObjectTypeDefinitionNode extends AbstractNode implements DefinitionNodeInterface
{

    use DescriptionTrait;
    use NameTrait;
    use DirectivesTrait;
    use InputFieldsTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::INPUT_OBJECT_TYPE_DEFINITION;

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
            'fields'      => $this->getFieldsAsArray(),
            'loc'         => $this->getLocationAsArray(),
        ];
    }
}
