<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\ArgumentsTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\DescriptionTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\NameTrait;
use Digia\GraphQL\Language\AST\Node\Contract\DefinitionNodeInterface;

class DirectiveDefinitionNode extends AbstractNode implements DefinitionNodeInterface
{

    use DescriptionTrait;
    use NameTrait;
    use ArgumentsTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::DIRECTIVE_DEFINITION;

    /**
     * @var NameNode[]
     */
    protected $locations;

    /**
     * @return NameNode[]
     */
    public function getLocations(): array
    {
        return $this->locations;
    }
}
