<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class NameASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::NAME;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $token = $this->expect($lexer, TokenKindEnum::NAME);

        return [
            'kind'  => NodeKindEnum::NAME,
            'value' => $token->getValue(),
            'loc'   => $this->buildLocation($lexer, $token),
        ];
    }
}
