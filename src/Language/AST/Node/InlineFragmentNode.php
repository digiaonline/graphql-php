<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\DirectivesTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\KindTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\LocationTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\SelectionSetTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\TypeConditionTrait;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\ConfigObject;

class InlineFragmentNode extends ConfigObject implements NodeInterface
{

    use KindTrait;
    use LocationTrait;
    use DirectivesTrait;
    use TypeConditionTrait;
    use SelectionSetTrait;

    /**
     * @var string
     */
    protected $kind = KindEnum::INLINE_FRAGMENT;
}
