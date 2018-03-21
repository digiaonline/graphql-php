<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class ArgumentsASTBuilder extends AbstractASTBuilder
{
    /**
     * @param LexerInterface $lexer
     * @return bool
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::ARGUMENTS;
    }

    /**
     * @param LexerInterface $lexer
     * @param array          $params
     * @return array
     * @throws SyntaxErrorException
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $isConst = $params['isConst'] ?? false;

        return $this->peek($lexer, TokenKindEnum::PAREN_L)
            ? $this->many(
                $lexer,
                TokenKindEnum::PAREN_L,
                [$this, $isConst ? 'parseConstArgument' : 'parseArgument'],
                TokenKindEnum::PAREN_R
            )
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseArgument(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        /**
         * @param LexerInterface $lexer
         * @return mixed
         * @throws SyntaxErrorException
         */
        $parseValue = function (LexerInterface $lexer) {
            $this->expect($lexer, TokenKindEnum::COLON);
            return $this->buildAST(ASTKindEnum::VALUE_LITERAL, $lexer);
        };

        return [
            'kind'  => NodeKindEnum::ARGUMENT,
            'name'  => $this->buildAST(ASTKindEnum::NAME, $lexer),
            'value' => $parseValue($lexer),
            'loc'   => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseConstArgument(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        /**
         * @param LexerInterface $lexer
         * @return mixed
         * @throws SyntaxErrorException
         */
        $parseValue = function (LexerInterface $lexer) {
            $this->expect($lexer, TokenKindEnum::COLON);
            return $this->buildAST(ASTKindEnum::VALUE_LITERAL, $lexer);
        };

        return [
            'kind'  => NodeKindEnum::ARGUMENT,
            'name'  => $this->buildAST(NodeKindEnum::NAME, $lexer),
            'value' => $parseValue($lexer),
            'loc'   => $this->buildLocation($lexer, $start),
        ];
    }
}
