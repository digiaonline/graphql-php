<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\InlineFragmentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

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
