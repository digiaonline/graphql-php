<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\TokenKindEnum;

class UnionMemberTypesASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::UNION_MEMBER_TYPES;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $types = [];

        if ($this->skip($lexer, TokenKindEnum::EQUALS)) {
            // Optional leading pipe
            $this->skip($lexer, TokenKindEnum::PIPE);

            do {
                $types[] = $this->buildAST(ASTKindEnum::NAMED_TYPE, $lexer);
            } while ($this->skip($lexer, TokenKindEnum::PIPE));
        }

        return $types;
    }
}
