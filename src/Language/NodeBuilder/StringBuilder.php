<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\StringValueNode;

class StringBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new StringValueNode([
            'value'    => $this->get($ast, 'value'),
            'block'    => $this->get($ast, 'block', false),
            'location' => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::STRING;
    }
}
