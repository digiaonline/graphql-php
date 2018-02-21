<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\DescriptionTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\DirectivesTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\NameTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\ValuesTrait;
use Digia\GraphQL\Language\AST\Node\Contract\DefinitionNodeInterface;

class EnumTypeDefinitionNode extends AbstractNode implements DefinitionNodeInterface
{

    use DescriptionTrait;
    use NameTrait;
    use DirectivesTrait;
    use ValuesTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::ENUM_TYPE_DEFINITION;
}
