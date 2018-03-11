<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Config\ConfigObject;
use Digia\GraphQL\Language\Node\EnumValueDefinitionNode;
use Digia\GraphQL\Language\Node\NodeTrait;

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
