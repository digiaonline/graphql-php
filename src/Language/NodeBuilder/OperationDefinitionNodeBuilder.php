<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;

class OperationDefinitionNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new OperationDefinitionNode([
            'operation'           => $this->getValue($ast, 'operation'),
            'name'                => $this->buildNode($ast, 'name'),
            'variableDefinitions' => $this->buildNodes($ast, 'variableDefinitions'),
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
        return $kind === NodeKindEnum::OPERATION_DEFINITION;
    }
}
