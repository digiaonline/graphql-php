<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\EnumValueDefinitionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class EnumValueDefinitionBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new EnumValueDefinitionNode([
            'description' => $this->buildOne($ast, 'description'),
            'name'        => $this->buildOne($ast, 'name'),
            'directives'  => $this->buildMany($ast, 'directives'),
            'location'    => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::ENUM_VALUE_DEFINITION;
    }
}
