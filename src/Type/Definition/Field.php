<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Config\ConfigAwareInterface;
use Digia\GraphQL\Config\ConfigAwareTrait;
use Digia\GraphQL\Language\Node\NodeAwareInterface;
use Digia\GraphQL\Language\Node\NodeTrait;

class Field implements ConfigAwareInterface, NodeAwareInterface, ArgumentsAwareInterface
{
    use ConfigAwareTrait;
    use NameTrait;
    use DescriptionTrait;
    use TypeTrait;
    use ArgumentsTrait;
    use DeprecationTrait;
    use ResolveTrait;
    use NodeTrait;
}
