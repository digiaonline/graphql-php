<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class ArgumentBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new ArgumentNode([
            'name'     => $this->buildOne($ast, 'name'),
            'value'    => $this->buildOne($ast, 'value'),
            'location' => $this->createLocation($ast),
        ]);
    }

    /**
     * @param string $kind
     * @return bool
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::ARGUMENT;
    }
}
