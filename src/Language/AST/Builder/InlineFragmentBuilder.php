<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Node\InlineFragmentNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class InlineFragmentBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new InlineFragmentNode([
            'typeCondition' => $this->buildOne($ast, 'typeCondition'),
            'directives'    => $this->buildMany($ast, 'directives'),
            'selectionSet'  => $this->buildOne($ast, 'selectionSet'),
            'location'      => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::INLINE_FRAGMENT;
    }
}
