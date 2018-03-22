<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\LexerInterface;

class DescriptionASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::DESCRIPTION;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        return $this->peekDescription($lexer)
            ? $this->buildAST(ASTKindEnum::STRING_LITERAL, $lexer)
            : null;
    }
}
