<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Language\AST\Node\InputValueDefinitionNode;

/**
 * Class Argument
 *
 * @package Digia\GraphQL\Type\Definition
 * @property InputValueDefinitionNode $astNode
 */
class Argument
{

    use NameTrait;
    use DescriptionTrait;
    use TypeTrait;
    use DefaultValueTrait;
    use NodeTrait;
    use ConfigTrait;
}
