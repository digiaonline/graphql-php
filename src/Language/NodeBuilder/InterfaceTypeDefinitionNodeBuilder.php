<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\InterfaceTypeDefinitionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class InterfaceTypeDefinitionNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new InterfaceTypeDefinitionNode([
            'description' => $this->buildNode($ast, 'description'),
            'name'        => $this->buildNode($ast, 'name'),
            'directives'  => $this->buildNodes($ast, 'directives'),
            'fields'      => $this->buildNodes($ast, 'fields'),
            'location'    => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === NodeKindEnum::INTERFACE_TYPE_DEFINITION;
    }
}
