<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\DirectiveNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class DirectiveBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new DirectiveNode([
            'name'      => $this->buildOne($ast, 'name'),
            'arguments' => $this->buildMany($ast, 'arguments'),
            'location'  => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::DIRECTIVE;
    }
}
