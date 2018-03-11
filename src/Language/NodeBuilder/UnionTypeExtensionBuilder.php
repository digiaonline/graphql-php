<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\UnionTypeExtensionNode;

class UnionTypeExtensionBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new UnionTypeExtensionNode([
            'name'       => $this->buildOne($ast, 'name'),
            'directives' => $this->buildMany($ast, 'directives'),
            'types'      => $this->buildMany($ast, 'types'),
            'location'   => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::UNION_TYPE_EXTENSION;
    }
}
