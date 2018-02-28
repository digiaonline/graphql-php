<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\Node\ArgumentsTrait;
use Digia\GraphQL\Language\AST\Node\DescriptionTrait;
use Digia\GraphQL\Language\AST\Node\DirectivesTrait;
use Digia\GraphQL\Language\AST\Node\NameTrait;
use Digia\GraphQL\Language\AST\Node\TypeTrait;
use Digia\GraphQL\Language\AST\Node\DefinitionNodeInterface;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class FieldDefinitionNode extends AbstractNode implements DefinitionNodeInterface
{

    use DescriptionTrait;
    use NameTrait;
    use ArgumentsTrait;
    use TypeTrait;
    use DirectivesTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::FIELD_DEFINITION;

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'        => $this->kind,
            'description' => $this->description,
            'name'        => $this->getNameAsArray(),
            'arguments'   => $this->getArgumentsAsArray(),
            'type'        => $this->getTypeAsArray(),
            'directives'  => $this->getDirectivesAsArray(),
            'loc'         => $this->getLocationAsArray(),
        ];
    }
}
