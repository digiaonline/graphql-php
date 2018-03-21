<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\UnionTypeExtensionNode;

class UnionTypeExtensionNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new UnionTypeExtensionNode([
            'name'       => $this->buildNode($ast, 'name'),
            'directives' => $this->buildNodes($ast, 'directives'),
            'types'      => $this->buildNodes($ast, 'types'),
            'location'   => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === NodeKindEnum::UNION_TYPE_EXTENSION;
    }
}
