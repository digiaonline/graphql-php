<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\DefaultValueTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\DescriptionTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\DirectivesTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\KindTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\LocationTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\NameTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\TypeTrait;
use Digia\GraphQL\Language\AST\Node\Contract\DefinitionNodeInterface;
use Digia\GraphQL\ConfigObject;

class InputValueDefinitionNode extends ConfigObject implements DefinitionNodeInterface
{

    use KindTrait;
    use LocationTrait;
    use DescriptionTrait;
    use NameTrait;
    use TypeTrait;
    use DefaultValueTrait;
    use DirectivesTrait;

    /**
     * @var string
     */
    protected $kind = KindEnum::INPUT_VALUE_DEFINITION;
}
