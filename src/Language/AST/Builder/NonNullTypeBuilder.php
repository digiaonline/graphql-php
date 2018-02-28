<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\NonNullTypeNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class NonNullTypeBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new NonNullTypeNode([
            'type'     => $this->buildOne($ast, 'type'),
            'location' => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::NON_NULL_TYPE;
    }
}
