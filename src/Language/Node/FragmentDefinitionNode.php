<?php

namespace Digia\GraphQL\Language\Node;

class FragmentDefinitionNode extends AbstractNode implements ExecutableDefinitionNodeInterface,
    DirectivesAwareInterface, NameAwareInterface
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
