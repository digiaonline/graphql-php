<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Behavior\ConfigTrait;
use Digia\GraphQL\Type\Definition\Behavior\DescriptionTrait;
use Digia\GraphQL\Behavior\ValueTrait;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Language\AST\Node\EnumValueDefinitionNode;
use Digia\GraphQL\Type\Definition\Behavior\DeprecationTrait;
use Digia\GraphQL\Type\Definition\Behavior\NameTrait;

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

    /**
     *
     */
    protected function afterConfig(): void
    {
        if ($this->value === null) {
            $this->value = $this->getName();
        }
    }
}
