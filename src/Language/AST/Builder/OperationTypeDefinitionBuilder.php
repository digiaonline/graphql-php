<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\OperationTypeDefinitionNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class OperationTypeDefinitionBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new OperationTypeDefinitionNode([
            'operation' => $this->get($ast, 'operation'),
            'type'      => $this->buildOne($ast, 'type'),
            'location'  => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::OPERATION_TYPE_DEFINITION;
    }
}
