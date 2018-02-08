<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Behavior\ConfigTrait;
use Digia\GraphQL\Behavior\DescriptionTrait;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Type\Behavior\ArgumentsTrait;
use Digia\GraphQL\Type\Behavior\DeprecationTrait;
use Digia\GraphQL\Type\Behavior\NameTrait;
use Digia\GraphQL\Type\Behavior\ResolveTrait;
use Digia\GraphQL\Type\Behavior\TypeTrait;

class Field
{

    use NameTrait;
    use DescriptionTrait;
    use TypeTrait;
    use ArgumentsTrait;
    use DeprecationTrait;
    use ResolveTrait;
    use NodeTrait;
    use ConfigTrait;
}
