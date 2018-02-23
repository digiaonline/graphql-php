<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\StringValueNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class StringBuilder extends AbstractBuilder
{

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new StringValueNode([
            'value'    => $this->getOne($ast, 'value'),
            'block'    => $this->getOne($ast, 'block', false),
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
