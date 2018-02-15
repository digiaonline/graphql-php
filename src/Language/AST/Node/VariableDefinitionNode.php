<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\DefaultValueTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\KindTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\LocationTrait;
use Digia\GraphQL\Language\AST\Node\Contract\DefinitionNodeInterface;
use Digia\GraphQL\Language\AST\Node\Contract\TypeNodeInterface;
use Digia\GraphQL\ConfigObject;

class VariableDefinitionNode extends ConfigObject implements DefinitionNodeInterface
{
    use KindTrait;
    use LocationTrait;
    use DefaultValueTrait;

    /**
     * @var string
     */
    protected $kind = KindEnum::VARIABLE_DEFINITION;

    /**
     * @var VariableNode
     */
    protected $variable;

    /**
     * @var TypeNodeInterface
     */
    protected $type;
}
