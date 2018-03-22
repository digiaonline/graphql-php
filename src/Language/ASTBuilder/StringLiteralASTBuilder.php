<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class StringLiteralASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::STRING_LITERAL;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $token = $lexer->getToken();

        $lexer->advance();

        return [
            'kind'  => NodeKindEnum::STRING,
            'value' => $token->getValue(),
            'block' => $token->getKind() === TokenKindEnum::BLOCK_STRING,
            'loc'   => $this->buildLocation($lexer, $token),
        ];
    }
}
