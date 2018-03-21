<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class FieldASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::FIELD;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $start = $lexer->getToken();

        $nameOrAlias = $this->buildAST(ASTKindEnum::NAME, $lexer);

        if ($this->skip($lexer, TokenKindEnum::COLON)) {
            $alias = $nameOrAlias;
            $name  = $this->buildAST(ASTKindEnum::NAME, $lexer);
        } else {
            $name = $nameOrAlias;
        }

        return [
            'kind'         => NodeKindEnum::FIELD,
            'alias'        => $alias ?? null,
            'name'         => $name,
            'arguments'    => $this->buildAST(ASTKindEnum::ARGUMENTS, $lexer),
            'directives'   => $this->buildAST(ASTKindEnum::DIRECTIVES, $lexer, ['isConst' => false]),
            'selectionSet' => $this->peek($lexer, TokenKindEnum::BRACE_L)
                ? $this->buildAST(ASTKindEnum::SELECTION_SET, $lexer)
                : null,
            'loc'          => $this->buildLocation($lexer, $start),
        ];
    }
}
