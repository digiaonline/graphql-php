<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class FragmentDefinitionBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new FragmentDefinitionNode([
            'name'                => $this->buildOne($ast, 'name'),
            'variableDefinitions' => $this->buildMany($ast, 'variableDefinitions'),
            'typeCondition'       => $this->buildOne($ast, 'typeCondition'),
            'directives'          => $this->buildMany($ast, 'directives'),
            'selectionSet'        => $this->buildOne($ast, 'selectionSet'),
            'location'            => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::FRAGMENT_DEFINITION;
    }
}
