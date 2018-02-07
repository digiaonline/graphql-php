<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\AST\NodeTrait;
use Digia\GraphQL\Language\AST\Node\InputValueDefinitionNode;

/**
 * Class InputField
 *
 * @package Digia\GraphQL\Type\Definition
 * @property InputValueDefinitionNode $astNode
 */
class InputField
{

    use NameTrait;
    use DescriptionTrait;
    use TypeTrait;
    use DefaultValueTrait;
    use NodeTrait;
    use ConfigTrait;
}
