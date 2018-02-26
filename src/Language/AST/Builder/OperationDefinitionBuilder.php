<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;

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
