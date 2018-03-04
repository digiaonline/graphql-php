<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class FragmentDefinitionBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new FragmentDefinitionNode([
            'name' => $this->buildOne($ast, 'name'),
            'variableDefinitions' => $this->buildMany($ast, 'variableDefinitions'),
            'typeCondition' => $this->buildOne($ast, 'typeCondition'),
            'directives' => $this->buildMany($ast, 'directives'),
            'selectionSet' => $this->buildOne($ast, 'selectionSet'),
            'location' => $this->createLocation($ast),
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
