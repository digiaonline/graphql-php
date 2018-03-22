<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\DirectiveNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class DirectiveNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new DirectiveNode([
            'name'      => $this->buildNode($ast, 'name'),
            'arguments' => $this->buildNodes($ast, 'arguments'),
            'location'  => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === NodeKindEnum::DIRECTIVE;
    }
}
