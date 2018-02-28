<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\ConfigObject;
use Digia\GraphQL\Language\AST\Node\InputValueDefinitionNode;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Type\Definition\DefaultValueTrait;
use Digia\GraphQL\Type\Definition\DescriptionTrait;
use Digia\GraphQL\Type\Definition\NameTrait;
use Digia\GraphQL\Type\Definition\TypeTrait;

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
