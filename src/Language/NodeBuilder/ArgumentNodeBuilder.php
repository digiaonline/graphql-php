<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class ArgumentNodeBuilder extends AbstractNodeBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new ArgumentNode([
            'name'     => $this->buildNode($ast, 'name'),
            'value'    => $this->buildNode($ast, 'value'),
            'location' => $this->createLocation($ast),
        ]);
    }

    /**
     * @param string $kind
     * @return bool
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === NodeKindEnum::ARGUMENT;
    }
}
