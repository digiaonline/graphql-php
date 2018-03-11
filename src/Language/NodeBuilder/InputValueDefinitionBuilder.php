<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\InputValueDefinitionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class InputValueDefinitionBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new InputValueDefinitionNode([
            'description'  => $this->buildOne($ast, 'description'),
            'name'         => $this->buildOne($ast, 'name'),
            'type'         => $this->buildOne($ast, 'type'),
            'defaultValue' => $this->buildOne($ast, 'defaultValue'),
            'directives'   => $this->buildMany($ast, 'directives'),
            'location'     => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::INPUT_VALUE_DEFINITION;
    }
}
