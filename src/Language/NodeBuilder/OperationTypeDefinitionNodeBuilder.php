<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\OperationTypeDefinitionNode;

class OperationTypeDefinitionNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new OperationTypeDefinitionNode([
            'operation' => $this->getValue($ast, 'operation'),
            'type'      => $this->buildNode($ast, 'type'),
            'location'  => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === NodeKindEnum::OPERATION_TYPE_DEFINITION;
    }
}
