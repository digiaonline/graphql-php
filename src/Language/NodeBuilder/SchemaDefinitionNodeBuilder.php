<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\SchemaDefinitionNode;

class SchemaDefinitionNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new SchemaDefinitionNode([
            'directives'     => $this->buildNodes($ast, 'directives'),
            'operationTypes' => $this->buildNodes($ast, 'operationTypes'),
            'location'       => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === NodeKindEnum::SCHEMA_DEFINITION;
    }
}
