<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\OperationTypeDefinitionNode;

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
