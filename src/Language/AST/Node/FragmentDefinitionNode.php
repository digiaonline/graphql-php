<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\Node\SelectionSetTrait;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\NameTrait;
use Digia\GraphQL\Language\AST\Node\TypeConditionTrait;
use Digia\GraphQL\Language\AST\Node\VariableDefinitionsTrait;
use Digia\GraphQL\Language\AST\Node\ExecutableDefinitionNodeInterface;

class FragmentDefinitionNode extends AbstractNode implements ExecutableDefinitionNodeInterface
{

    use NameTrait;
    use TypeConditionTrait;
    use VariableDefinitionsTrait;
    use SelectionSetTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::FRAGMENT_DEFINITION;
}
