<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\Node\DefaultValueTrait;
use Digia\GraphQL\Language\AST\Node\DescriptionTrait;
use Digia\GraphQL\Language\AST\Node\DirectivesTrait;
use Digia\GraphQL\Language\AST\Node\NameTrait;
use Digia\GraphQL\Language\AST\Node\TypeTrait;
use Digia\GraphQL\Language\AST\Node\DefinitionNodeInterface;
use Digia\GraphQL\Language\AST\NodeKindEnum;

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
