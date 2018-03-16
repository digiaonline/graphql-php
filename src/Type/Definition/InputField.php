<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Config\ConfigObject;
use Digia\GraphQL\Language\Node\InputValueDefinitionNode;
use Digia\GraphQL\Language\Node\NodeAwareInterface;
use Digia\GraphQL\Language\Node\NodeTrait;

/**
 * Class InputField
 *
 * @package Digia\GraphQL\Type\Definition
 * @property InputValueDefinitionNode $astNode
 */
class InputField extends ConfigObject implements NodeAwareInterface
{
    use NameTrait;
    use DescriptionTrait;
    use TypeTrait;
    use DefaultValueTrait;
    use NodeTrait;
}
