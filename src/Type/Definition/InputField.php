<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Config\ConfigObject;
use Digia\GraphQL\Language\AST\Node\InputValueDefinitionNode;
use Digia\GraphQL\Language\AST\Node\NodeTrait;

/**
 * Class InputField
 *
 * @package Digia\GraphQL\Type\Definition
 * @property InputValueDefinitionNode $astNode
 */
class InputField extends ConfigObject
{

    use NameTrait;
    use DescriptionTrait;
    use TypeTrait;
    use DefaultValueTrait;
    use NodeTrait;
}
