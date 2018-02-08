<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Behavior\ConfigTrait;
use Digia\GraphQL\Behavior\DescriptionTrait;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Language\AST\Node\InputValueDefinitionNode;
use Digia\GraphQL\Type\Behavior\DefaultValueTrait;
use Digia\GraphQL\Type\Behavior\NameTrait;
use Digia\GraphQL\Type\Behavior\TypeTrait;

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
