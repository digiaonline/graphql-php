<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\FieldDefinitionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class FieldDefinitionNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new FieldDefinitionNode([
            'description' => $this->buildNode($ast, 'description'),
            'name'        => $this->buildNode($ast, 'name'),
            'arguments'   => $this->buildNodes($ast, 'arguments'),
            'type'        => $this->buildNode($ast, 'type'),
            'directives'  => $this->buildNodes($ast, 'directives'),
            'location'    => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === NodeKindEnum::FIELD_DEFINITION;
    }
}
