<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\AST\NodeTrait;
use Digia\GraphQL\Language\AST\Node\EnumValueDefinitionNode;

/**
 * Class EnumValue
 *
 * @package Digia\GraphQL\Type\Definition\Enum
 * @property EnumValueDefinitionNode $astNode
 */
class EnumValue
{

    use NameTrait;
    use DescriptionTrait;
    use DeprecationTrait;
    use ValueTrait;
    use NodeTrait;
    use ConfigTrait;
}
