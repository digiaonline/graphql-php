<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\InlineFragmentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class InlineFragmentNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new InlineFragmentNode([
            'typeCondition' => $this->buildNode($ast, 'typeCondition'),
            'directives'    => $this->buildNodes($ast, 'directives'),
            'selectionSet'  => $this->buildNode($ast, 'selectionSet'),
            'location'      => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === NodeKindEnum::INLINE_FRAGMENT;
    }
}
