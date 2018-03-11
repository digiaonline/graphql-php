<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\ObjectTypeDefinitionNode;

class ObjectTypeDefinitionBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new ObjectTypeDefinitionNode([
            'description' => $this->buildOne($ast, 'description'),
            'name'        => $this->buildOne($ast, 'name'),
            'interfaces'  => $this->buildMany($ast, 'interfaces'),
            'directives'  => $this->buildMany($ast, 'directives'),
            'fields'      => $this->buildMany($ast, 'fields'),
            'location'    => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::OBJECT_TYPE_DEFINITION;
    }
}
