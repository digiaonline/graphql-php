<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\InputObjectTypeExtensionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class InputObjectTypeExtensionBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new InputObjectTypeExtensionNode([
            'name'       => $this->buildOne($ast, 'name'),
            'directives' => $this->buildMany($ast, 'directives'),
            'fields'     => $this->buildMany($ast, 'fields'),
            'location'   => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::INPUT_OBJECT_TYPE_EXTENSION;
    }
}
