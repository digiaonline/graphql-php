<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class FragmentSpreadNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new FragmentSpreadNode([
            'name'         => $this->buildNode($ast, 'name'),
            'directives'   => $this->buildNodes($ast, 'directives'),
            'selectionSet' => $this->buildNode($ast, 'selectionSet'),
            'location'     => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === NodeKindEnum::FRAGMENT_SPREAD;
    }
}
