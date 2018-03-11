<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;

class VariableDefinitionBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new VariableDefinitionNode([
            'variable'     => $this->buildOne($ast, 'variable'),
            'type'         => $this->buildOne($ast, 'type'),
            'defaultValue' => $this->buildOne($ast, 'defaultValue'),
            'location'     => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::VARIABLE_DEFINITION;
    }
}
