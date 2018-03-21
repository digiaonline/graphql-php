<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\ObjectTypeExtensionNode;

class ObjectTypeExtensionNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new ObjectTypeExtensionNode([
            'name'       => $this->buildNode($ast, 'name'),
            'interfaces' => $this->buildNodes($ast, 'interfaces'),
            'directives' => $this->buildNodes($ast, 'directives'),
            'fields'     => $this->buildNodes($ast, 'fields'),
            'location'   => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === NodeKindEnum::OBJECT_TYPE_EXTENSION;
    }
}
