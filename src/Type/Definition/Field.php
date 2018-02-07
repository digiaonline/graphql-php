<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\AST\Node\NodeTrait;

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
