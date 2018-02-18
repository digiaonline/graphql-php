<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Builder\Behavior\ParseKindTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseNameTrait;
use Digia\GraphQL\Language\AST\Builder\Behavior\ParseValueLiteralTrait;
use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\ArgumentNode;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

class ArgumentNodeBuilder extends AbstractNodeBuilder
{

    use ParseKindTrait;
    use ParseNameTrait;
    use ParseValueLiteralTrait;

    /**
     * @param array $ast
     * @return NodeInterface
     */
    public function build(array $ast): NodeInterface
    {
        return new ArgumentNode([
            'kind'  => $this->parseKind($ast),
            'name'  => $this->parseName($ast),
            'value' => $this->parseValueLiteral($ast, false),
        ]);
    }

    /**
     * @param string $kind
     * @return bool
     */
    public function supportsKind(string $kind): bool
    {
        return KindEnum::ARGUMENT === $kind;
    }
}
