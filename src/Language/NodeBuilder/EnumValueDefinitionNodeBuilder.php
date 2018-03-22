<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\EnumValueDefinitionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class EnumValueDefinitionNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new EnumValueDefinitionNode([
            'description' => $this->buildNode($ast, 'description'),
            'name'        => $this->buildNode($ast, 'name'),
            'directives'  => $this->buildNodes($ast, 'directives'),
            'location'    => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === NodeKindEnum::ENUM_VALUE_DEFINITION;
    }
}
