<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\KindTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\LocationTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\NameTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\TypeConditionTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\VariableDefinitionsTrait;
use Digia\GraphQL\Language\AST\Node\Contract\ExecutableDefinitionNodeInterface;
use Digia\GraphQL\ConfigObject;

class FragmentDefinitionNode extends ConfigObject implements ExecutableDefinitionNodeInterface
{

    use KindTrait;
    use LocationTrait;
    use NameTrait;
    use TypeConditionTrait;
    use VariableDefinitionsTrait;

    /**
     * @var string
     */
    protected $kind = KindEnum::FRAGMENT_DEFINITION;
}
