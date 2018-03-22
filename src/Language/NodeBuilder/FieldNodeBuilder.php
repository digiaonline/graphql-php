<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class FieldNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new FieldNode([
            'alias'        => $this->buildNode($ast, 'alias'),
            'name'         => $this->buildNode($ast, 'name'),
            'arguments'    => $this->buildNodes($ast, 'arguments'),
            'directives'   => $this->buildNodes($ast, 'directives'),
            'selectionSet' => $this->buildNode($ast, 'selectionSet'),
            'location'     => $this->createLocation($ast),
        ]);
    }

    /**
     * @param string $kind
     * @return bool
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === NodeKindEnum::FIELD;
    }
}
