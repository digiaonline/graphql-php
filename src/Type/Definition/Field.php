<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\ConfigObject;
use Digia\GraphQL\Type\Definition\Behavior\DescriptionTrait;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Type\Definition\Behavior\ArgumentsTrait;
use Digia\GraphQL\Type\Definition\Behavior\DeprecationTrait;
use Digia\GraphQL\Type\Definition\Behavior\NameTrait;
use Digia\GraphQL\Type\Definition\Behavior\ResolveTrait;
use Digia\GraphQL\Type\Definition\Behavior\TypeTrait;

class Field extends ConfigObject
{

    use NameTrait;
    use DescriptionTrait;
    use TypeTrait;
    use ArgumentsTrait;
    use DeprecationTrait;
    use ResolveTrait;
    use NodeTrait;
}
