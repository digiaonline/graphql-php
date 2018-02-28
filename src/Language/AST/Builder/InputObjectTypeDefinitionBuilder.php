<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\InputObjectTypeDefinitionNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class InputObjectTypeDefinitionBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new InputObjectTypeDefinitionNode([
            'description' => $this->buildOne($ast, 'description'),
            'name'        => $this->buildOne($ast, 'name'),
            'directives'  => $this->buildMany($ast, 'directives'),
            'fields'      => $this->buildMany($ast, 'fields'),
            'location'    => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::INPUT_OBJECT_TYPE_DEFINITION;
    }
}
