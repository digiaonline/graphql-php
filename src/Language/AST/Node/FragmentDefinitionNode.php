<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;

class FragmentDefinitionNode extends AbstractNode implements ExecutableDefinitionNodeInterface
{

    use NameTrait;
    use VariableDefinitionsTrait;
    use TypeConditionTrait;
    use DirectivesTrait;
    use SelectionSetTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::FRAGMENT_DEFINITION;
}
