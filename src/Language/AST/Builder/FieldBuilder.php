<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class FieldBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new FieldNode([
            'alias'        => $this->buildOne($ast, 'alias'),
            'name'         => $this->buildOne($ast, 'name'),
            'arguments'    => $this->buildMany($ast, 'arguments'),
            'directives'   => $this->buildMany($ast, 'directives'),
            'selectionSet' => $this->buildOne($ast, 'selectionSet'),
            'location'     => $this->createLocation($ast),
        ]);
    }

    /**
     * @param string $kind
     * @return bool
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::FIELD;
    }
}
