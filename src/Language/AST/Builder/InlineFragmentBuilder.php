<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Builder\Behavior\ParseDirectivesTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseKindTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseLocationTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseNameTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseSelectionSetTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseTypeConditionTrait;
use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\InlineFragmentNode;

class InlineFragmentBuilder extends AbstractBuilder
{

    use ParseKindTrait;
    use ParseNameTrait;
    use ParseTypeConditionTrait;
    use ParseDirectivesTrait;
    use ParseSelectionSetTrait;
    use ParseLocationTrait;

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new InlineFragmentNode([
            'kind'          => $this->parseKind($ast),
            'typeCondition' => $this->parseTypeCondition($ast),
            'directives'    => $this->parseDirectives($ast),
            'selectionSet'  => $this->parseSelectionSet($ast),
            'loc'           => $this->parseLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === KindEnum::INLINE_FRAGMENT;
    }
}
