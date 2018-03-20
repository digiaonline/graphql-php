<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Config\ConfigAwareInterface;
use Digia\GraphQL\Config\ConfigAwareTrait;
use Digia\GraphQL\Language\Node\NodeAwareInterface;
use Digia\GraphQL\Language\Node\NodeTrait;

class EnumValue implements ConfigAwareInterface, NodeAwareInterface
{
    use ConfigAwareTrait;
    use NameTrait;
    use DescriptionTrait;
    use DeprecationTrait;
    use ValueTrait;
    use NodeTrait;

    /**
     * @inheritdoc
     */
    protected function afterConfig(): void
    {
        // By default, enum values use their value as their name.
        if ($this->value === null) {
            $this->value = $this->getName();
        }
    }
}
