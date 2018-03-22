<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;

class VariableDefinitionNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new VariableDefinitionNode([
            'variable'     => $this->buildNode($ast, 'variable'),
            'type'         => $this->buildNode($ast, 'type'),
            'defaultValue' => $this->buildNode($ast, 'defaultValue'),
            'location'     => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === NodeKindEnum::VARIABLE_DEFINITION;
    }
}
