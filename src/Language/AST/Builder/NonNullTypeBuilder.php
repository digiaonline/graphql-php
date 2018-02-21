<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Builder\Behavior\ParseLocationTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseTypeTrait;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\NonNullTypeNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class NonNullTypeBuilder extends AbstractBuilder
{

    use ParseTypeTrait;
    use ParseLocationTrait;

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new NonNullTypeNode([
            'type' => $this->parseType($ast),
            'loc'  => $this->parseLocation($ast),
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
