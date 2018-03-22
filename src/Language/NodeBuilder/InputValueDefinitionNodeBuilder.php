<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\InputValueDefinitionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class InputValueDefinitionNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new InputValueDefinitionNode([
            'description'  => $this->buildNode($ast, 'description'),
            'name'         => $this->buildNode($ast, 'name'),
            'type'         => $this->buildNode($ast, 'type'),
            'defaultValue' => $this->buildNode($ast, 'defaultValue'),
            'directives'   => $this->buildNodes($ast, 'directives'),
            'location'     => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === NodeKindEnum::INPUT_VALUE_DEFINITION;
    }
}
