<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class FieldBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new FieldNode([
            'alias'        => $this->buildOne($ast, 'alias'),
            'name'         => $this->buildOne($ast, 'name'),
            'arguments'    => $this->buildMany($ast, 'arguments'),
            'directives'   => $this->buildMany($ast, 'directives'),
            'selectionSet' => $this->buildOne($ast, 'selectionSet'),
            'location'     => $this->createLocation($ast),
        ]);
    }

    /**
     * @param string $kind
     * @return bool
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::FIELD;
    }
}
