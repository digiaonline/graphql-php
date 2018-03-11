<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Config\ConfigObject;
use Digia\GraphQL\Language\Node\NodeTrait;

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
