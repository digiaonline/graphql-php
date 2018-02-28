<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\ObjectTypeExtensionNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class ObjectTypeExtensionBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new ObjectTypeExtensionNode([
            'name'       => $this->buildOne($ast, 'name'),
            'interfaces' => $this->buildMany($ast, 'interfaces'),
            'directives' => $this->buildMany($ast, 'directives'),
            'fields'     => $this->buildMany($ast, 'fields'),
            'location'   => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::OBJECT_TYPE_EXTENSION;
    }
}
