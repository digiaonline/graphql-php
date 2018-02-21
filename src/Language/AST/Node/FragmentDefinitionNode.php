<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\NameTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\TypeConditionTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\VariableDefinitionsTrait;
use Digia\GraphQL\Language\AST\Node\Contract\ExecutableDefinitionNodeInterface;

class FragmentDefinitionNode extends AbstractNode implements ExecutableDefinitionNodeInterface
{

    use NameTrait;
    use TypeConditionTrait;
    use VariableDefinitionsTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::FRAGMENT_DEFINITION;
}
