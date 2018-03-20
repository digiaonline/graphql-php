<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\TokenKindEnum;

class InputFieldsDefinitionASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::INPUT_FIELDS_DEFINITION;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $parseFunction = function (LexerInterface $lexer): array {
            return $this->buildAST(ASTKindEnum::INPUT_VALUE_DEFINITION, $lexer);
        };

        return $this->peek($lexer, TokenKindEnum::BRACE_L)
            ? $this->many(
                $lexer,
                TokenKindEnum::BRACE_L,
                $parseFunction,
                TokenKindEnum::BRACE_R
            )
            : [];
    }
}
