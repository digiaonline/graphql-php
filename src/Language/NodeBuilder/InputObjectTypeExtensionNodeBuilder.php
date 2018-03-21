<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\InputObjectTypeExtensionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class InputObjectTypeExtensionNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new InputObjectTypeExtensionNode([
            'name'       => $this->buildNode($ast, 'name'),
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
        return $kind === NodeKindEnum::INPUT_OBJECT_TYPE_EXTENSION;
    }
}
