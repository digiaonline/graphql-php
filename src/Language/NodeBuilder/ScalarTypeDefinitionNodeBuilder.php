<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\ScalarTypeDefinitionNode;

class ScalarTypeDefinitionNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new ScalarTypeDefinitionNode([
            'description' => $this->buildNode($ast, 'description'),
            'name'        => $this->buildNode($ast, 'name'),
            'directives'  => $this->buildNodes($ast, 'directives'),
            'location'    => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === NodeKindEnum::SCALAR_TYPE_DEFINITION;
    }
}
