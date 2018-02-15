<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\KindTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\LocationTrait;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\Contract\SelectionNodeInterface;
use Digia\GraphQL\ConfigObject;

class SelectionSetNode extends ConfigObject implements NodeInterface
{

    use KindTrait;
    use LocationTrait;

    /**
     * @var string
     */
    protected $kind = KindEnum::SELECTION_SET;

    /**
     * @var SelectionNodeInterface[]
     */
    protected $selections;
}
