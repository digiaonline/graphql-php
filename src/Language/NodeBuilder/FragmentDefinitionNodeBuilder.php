<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class FragmentDefinitionNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new FragmentDefinitionNode([
            'name'                => $this->buildNode($ast, 'name'),
            'variableDefinitions' => $this->buildNodes($ast, 'variableDefinitions'),
            'typeCondition'       => $this->buildNode($ast, 'typeCondition'),
            'directives'          => $this->buildNodes($ast, 'directives'),
            'selectionSet'        => $this->buildNode($ast, 'selectionSet'),
            'location'            => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === NodeKindEnum::FRAGMENT_DEFINITION;
    }
}
