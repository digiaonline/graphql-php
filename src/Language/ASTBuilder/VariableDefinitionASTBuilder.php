<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class VariableDefinitionASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::VARIABLE_DEFINITION;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        return $this->peek($lexer, TokenKindEnum::PAREN_L)
            ? $this->many($lexer, TokenKindEnum::PAREN_L, [$this, 'parseVariableDefinition'], TokenKindEnum::PAREN_R)
            : [];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseVariableDefinition(LexerInterface $lexer): array
    {
        $start = $lexer->getToken();

        /**
         * @param LexerInterface $lexer
         * @return mixed
         * @throws SyntaxErrorException
         */
        $parseType = function (LexerInterface $lexer) {
            $this->expect($lexer, TokenKindEnum::COLON);
            return $this->buildAST(ASTKindEnum::TYPE_REFERENCE, $lexer);
        };

        return [
            'kind'         => NodeKindEnum::VARIABLE_DEFINITION,
            'variable'     => $this->buildAST(ASTKindEnum::VARIABLE, $lexer),
            'type'         => $parseType($lexer),
            'defaultValue' => $this->skip($lexer, TokenKindEnum::EQUALS)
                ? $this->buildAST(ASTKindEnum::VALUE_LITERAL, $lexer, ['isConst' => true])
                : null,
            'loc'          => $this->buildLocation($lexer, $start),
        ];
    }
}
