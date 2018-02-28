<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\ConfigObject;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Type\Definition\ArgumentsTrait;
use Digia\GraphQL\Type\Definition\DeprecationTrait;
use Digia\GraphQL\Type\Definition\DescriptionTrait;
use Digia\GraphQL\Type\Definition\NameTrait;
use Digia\GraphQL\Type\Definition\ResolveTrait;
use Digia\GraphQL\Type\Definition\TypeTrait;

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
