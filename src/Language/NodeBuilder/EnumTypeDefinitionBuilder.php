<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\EnumTypeDefinitionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class EnumTypeDefinitionBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new EnumTypeDefinitionNode([
            'description' => $this->buildOne($ast, 'description'),
            'name'        => $this->buildOne($ast, 'name'),
            'directives'  => $this->buildMany($ast, 'directives'),
            'values'      => $this->buildMany($ast, 'values'),
            'location'    => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::ENUM_TYPE_DEFINITION;
    }
}
