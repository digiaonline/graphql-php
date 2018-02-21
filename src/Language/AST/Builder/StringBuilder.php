<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Builder\Behavior\ParseKindTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseLocationTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseValueTrait;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\IntValueNode;
use Digia\GraphQL\Language\AST\Node\StringValueNode;
use Digia\GraphQL\Language\TokenKindEnum;

class StringBuilder extends AbstractBuilder
{

    use ParseKindTrait;
    use ParseValueTrait;
    use ParseLocationTrait;

    /**
     * @inheritdoc
     */
    public function build(array $ast): NodeInterface
    {
        return new StringValueNode([
            'kind'  => $this->parseKind($ast),
            'value' => $this->parseValue($ast),
            'block' => $this->parseBlock($ast),
            'loc'   => $this->parseLocation($ast),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supportsKind(string $kind): bool
    {
        return $kind === NodeKindEnum::STRING;
    }

    /**
     * @param array $ast
     * @return bool
     */
    protected function parseBlock(array $ast): bool
    {
        return $ast['block'] ?? false;
    }
}
