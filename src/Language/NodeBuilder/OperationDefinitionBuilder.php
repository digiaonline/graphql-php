<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;

class OperationDefinitionBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new OperationDefinitionNode([
            'operation'           => $this->get($ast, 'operation'),
            'name'                => $this->buildOne($ast, 'name'),
            'variableDefinitions' => $this->buildMany($ast, 'variableDefinitions'),
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
        return $kind === NodeKindEnum::OPERATION_DEFINITION;
    }
}
