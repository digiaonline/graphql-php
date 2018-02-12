<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Behavior\ConfigTrait;
use Digia\GraphQL\Type\Definition\Behavior\DescriptionTrait;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Type\Definition\Behavior\ArgumentsTrait;
use Digia\GraphQL\Type\Definition\Behavior\DeprecationTrait;
use Digia\GraphQL\Type\Definition\Behavior\NameTrait;
use Digia\GraphQL\Type\Definition\Behavior\ResolveTrait;
use Digia\GraphQL\Type\Definition\Behavior\TypeTrait;
use function Digia\GraphQL\Type\isValidResolver;
use function Digia\GraphQL\Util\invariant;
use function Digia\GraphQL\Util\toString;

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
