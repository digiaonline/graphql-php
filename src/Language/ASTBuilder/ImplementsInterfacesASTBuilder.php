<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class ImplementsInterfacesASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::IMPLEMENTS_INTERFACES;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $types = [];

        if ($lexer->getTokenValue() === 'implements') {
            $lexer->advance();

            // Optional leading ampersand
            $this->skip($lexer, TokenKindEnum::AMP);

            do {
                $types[] = $this->buildAST(ASTKindEnum::NAMED_TYPE, $lexer);
            } while ($this->skip($lexer, TokenKindEnum::AMP));
        }

        return $types;
    }
}
