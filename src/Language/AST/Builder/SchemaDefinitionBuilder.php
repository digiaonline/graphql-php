<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\SchemaDefinitionNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;

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
