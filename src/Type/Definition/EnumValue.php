<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\ConfigObject;
use Digia\GraphQL\Language\AST\Node\EnumValueDefinitionNode;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Type\Definition\DeprecationTrait;
use Digia\GraphQL\Type\Definition\DescriptionTrait;
use Digia\GraphQL\Type\Definition\NameTrait;
use Digia\GraphQL\Type\Definition\ValueTrait;

/**
 * Class EnumValue
 *
 * @package Digia\GraphQL\Type\Definition\Enum
 * @property EnumValueDefinitionNode $astNode
 */
class EnumValue extends ConfigObject
{

    use NameTrait;
    use DescriptionTrait;
    use DeprecationTrait;
    use ValueTrait;
    use NodeTrait;

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
