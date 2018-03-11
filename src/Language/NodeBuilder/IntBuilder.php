<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\IntValueNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;

class IntBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new IntValueNode([
            'value'    => $this->get($ast, 'value'),
            'location' => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::INT;
    }
}
