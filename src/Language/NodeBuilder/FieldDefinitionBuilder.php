<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\FieldDefinitionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class FieldDefinitionBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new FieldDefinitionNode([
            'description' => $this->buildOne($ast, 'description'),
            'name'        => $this->buildOne($ast, 'name'),
            'arguments'   => $this->buildMany($ast, 'arguments'),
            'type'        => $this->buildOne($ast, 'type'),
            'directives'  => $this->buildMany($ast, 'directives'),
            'location'    => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::FIELD_DEFINITION;
    }
}
