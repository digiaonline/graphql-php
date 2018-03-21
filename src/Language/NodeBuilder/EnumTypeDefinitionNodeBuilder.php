<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\EnumTypeDefinitionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class EnumTypeDefinitionNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new EnumTypeDefinitionNode([
            'description' => $this->buildNode($ast, 'description'),
            'name'        => $this->buildNode($ast, 'name'),
            'directives'  => $this->buildNodes($ast, 'directives'),
            'values'      => $this->buildNodes($ast, 'values'),
            'location'    => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === NodeKindEnum::ENUM_TYPE_DEFINITION;
    }
}
