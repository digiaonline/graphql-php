<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\InterfaceTypeDefinitionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class InterfaceTypeDefinitionBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new InterfaceTypeDefinitionNode([
            'description' => $this->buildOne($ast, 'description'),
            'name'        => $this->buildOne($ast, 'name'),
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
        return $kind === NodeKindEnum::INTERFACE_TYPE_DEFINITION;
    }
}
