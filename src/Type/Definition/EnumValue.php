<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Config\ConfigObject;
use Digia\GraphQL\Language\Node\NodeAwareInterface;
use Digia\GraphQL\Language\Node\NodeTrait;

class EnumValue extends ConfigObject implements NodeAwareInterface
{
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
