<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\ObjectFieldNode;

class ObjectFieldBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new ObjectFieldNode([
            'name'     => $this->buildOne($ast, 'name'),
            'value'    => $this->buildOne($ast, 'value'),
            'location' => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::OBJECT_FIELD;
    }
}
