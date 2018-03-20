<?php

namespace Digia\GraphQL\Language\ASTBuilder;


use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class ValueLiteralASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::VALUE_LITERAL;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        return $this->parseValueLiteral($lexer, $params['isConst'] ?? false);
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseValueLiteral(LexerInterface $lexer, bool $isConst): array
    {
        $token = $lexer->getToken();

        switch ($token->getKind()) {
            case TokenKindEnum::BRACKET_L:
                return $this->parseList($lexer, $isConst);
            case TokenKindEnum::BRACE_L:
                return $this->parseObject($lexer, $isConst);
            case TokenKindEnum::INT:
                $lexer->advance();

                return [
                    'kind'  => NodeKindEnum::INT,
                    'value' => $token->getValue(),
                    'loc'   => $this->buildLocation($lexer, $token),
                ];
            case TokenKindEnum::FLOAT:
                $lexer->advance();

                return [
                    'kind'  => NodeKindEnum::FLOAT,
                    'value' => $token->getValue(),
                    'loc'   => $this->buildLocation($lexer, $token),
                ];
            case TokenKindEnum::STRING:
            case TokenKindEnum::BLOCK_STRING:
                return $this->buildAST(ASTKindEnum::STRING_LITERAL, $lexer);
            case TokenKindEnum::NAME:
                $value = $token->getValue();

                if ($value === 'true' || $value === 'false') {
                    $lexer->advance();

                    return [
                        'kind'  => NodeKindEnum::BOOLEAN,
                        'value' => $value === 'true',
                        'loc'   => $this->buildLocation($lexer, $token),
                    ];
                }

                if ($value === 'null') {
                    $lexer->advance();

                    return [
                        'kind' => NodeKindEnum::NULL,
                        'loc'  => $this->buildLocation($lexer, $token),
                    ];
                }

                $lexer->advance();

                return [
                    'kind'  => NodeKindEnum::ENUM,
                    'value' => $token->getValue(),
                    'loc'   => $this->buildLocation($lexer, $token),
                ];
            case TokenKindEnum::DOLLAR:
                if (!$isConst) {
                    return $this->buildAST(ASTKindEnum::VARIABLE, $lexer);
                }
                break;
        }

        throw $this->unexpected($lexer);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseConstValue(LexerInterface $lexer): array
    {
        return $this->parseValueLiteral($lexer, true);
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseValue(LexerInterface $lexer): array
    {
        return $this->parseValueLiteral($lexer, false);
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseList(LexerInterface $lexer, bool $isConst): array
    {
        $start = $lexer->getToken();

        return [
            'kind'   => NodeKindEnum::LIST,
            'values' => $this->any(
                $lexer,
                TokenKindEnum::BRACKET_L,
                [$this, $isConst ? 'parseConstValue' : 'parseValue'],
                TokenKindEnum::BRACKET_R
            ),
            'loc'    => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseObject(LexerInterface $lexer, bool $isConst): array
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::BRACE_L);

        $fields = [];

        while (!$this->skip($lexer, TokenKindEnum::BRACE_R)) {
            $fields[] = $this->parseObjectField($lexer, $isConst);
        }

        return [
            'kind'   => NodeKindEnum::OBJECT,
            'fields' => $fields,
            'loc'    => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseObjectField(LexerInterface $lexer, bool $isConst): array
    {
        $start = $lexer->getToken();

        $parseValue = function (LexerInterface $lexer, bool $isConst) {
            $this->expect($lexer, TokenKindEnum::COLON);

            return $this->parseValueLiteral($lexer, $isConst);
        };

        return [
            'kind'  => NodeKindEnum::OBJECT_FIELD,
            'name'  => $this->buildAST(ASTKindEnum::NAME, $lexer),
            'value' => $parseValue($lexer, $isConst),
            'loc'   => $this->buildLocation($lexer, $start),
        ];
    }
}
