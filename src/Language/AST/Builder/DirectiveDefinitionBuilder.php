<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class DirectiveDefinitionBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new DirectiveDefinitionNode([
            'description' => $this->buildOne($ast, 'description'),
            'name'        => $this->buildOne($ast, 'name'),
            'arguments'   => $this->buildMany($ast, 'arguments'),
            'locations'   => $this->buildMany($ast, 'locations'),
            'location'    => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::DIRECTIVE_DEFINITION;
    }
}
