<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\SchemaDefinitionNode;

class SchemaDefinitionBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new SchemaDefinitionNode([
            'directives'     => $this->buildMany($ast, 'directives'),
            'operationTypes' => $this->buildMany($ast, 'operationTypes'),
            'location'       => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::SCHEMA_DEFINITION;
    }
}
