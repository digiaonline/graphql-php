<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\SelectionSetNode;

class SelectionSetBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new SelectionSetNode([
            'selections' => $this->buildMany($ast, 'selections'),
            'location'   => $this->createLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return NodeKindEnum::SELECTION_SET === $kind;
    }
}
