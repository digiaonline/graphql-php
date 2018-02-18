<?php

namespace Digia\GraphQL\Language\AST\Builder\Behavior;

use Digia\GraphQL\Language\AST\Node\Contract\ValueNodeInterface;
use Digia\GraphQL\Language\AST\Node\FloatValueNode;
use Digia\GraphQL\Language\AST\Node\IntValueNode;
use Digia\GraphQL\Language\AST\Node\ListValueNode;
use Digia\GraphQL\Language\AST\Node\ObjectValueNode;
use Digia\GraphQL\Language\AST\Node\StringValueNode;
use Digia\GraphQL\Language\TokenKindEnum;

trait ParseValueLiteralTrait
{

    use ParseLocationTrait;

    /**
     * @param $ast
     * @param $isConst
     * @return ValueNodeInterface
     */
    protected function parseValueLiteral($ast, $isConst): ValueNodeInterface
    {
        $valueAst = $ast['value'];

        switch ($valueAst['kind']) {
            case TokenKindEnum::BRACE_L:
                return $this->parseList($valueAst, $isConst);
            case TokenKindEnum::BRACE_R:
                return $this->parseObject($valueAst, $isConst);
            case TokenKindEnum::INT:
                return $this->parseInt($valueAst);
            case TokenKindEnum::FLOAT:
                return $this->parseFloat($valueAst);
            case TokenKindEnum::STRING:
            case TokenKindEnum::BLOCK_STRING:
                return $this->parseStringLiteral($valueAst);
            case TokenKindEnum::NAME:
                return $this->parseName($valueAst);
            case TokenKindEnum::DOLLAR:
                if (!$isConst) {
                    return $this->parseVariable($valueAst);
                }
                break;
            default:
                var_dump($valueAst['kind']);die;
                break;
        }

        // TODO: Throw exception
    }

    protected function parseList(array $ast, $isConst): ListValueNode
    {
        var_dump($ast['kind']);die;
    }

    protected function parseObject(array $ast, $isConst): ObjectValueNode
    {
        var_dump($ast['kind']);die;
    }

    protected function parseIntLiteral(array $ast): IntValueNode
    {
        var_dump($ast['kind']);die;
    }

    protected function parseFloatLiteral(array $ast): FloatValueNode
    {
        var_dump($ast['kind']);die;
    }

    protected function parseStringLiteral(array $ast): StringValueNode
    {
        return new StringValueNode([
            'kind' => $this->parseKind($ast),
            'value' => $ast['value'],
            'block' => TokenKindEnum::BLOCK_STRING === $ast['kind'],
            'loc' => $this->parseLocation($ast),
        ]);
    }

    protected function parseNameLiteral(array $ast): ValueNodeInterface
    {
        var_dump($ast['kind']);die;
    }

    protected function parseVariableLiteral(array $ast): ValueNodeInterface
    {
        var_dump($ast['kind']);die;
    }
}
