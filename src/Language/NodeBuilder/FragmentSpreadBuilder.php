<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class FragmentSpreadBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new FragmentSpreadNode([
            'name'         => $this->buildOne($ast, 'name'),
            'directives'   => $this->buildMany($ast, 'directives'),
            'selectionSet' => $this->buildOne($ast, 'selectionSet'),
            'location'     => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::FRAGMENT_SPREAD;
    }
}
