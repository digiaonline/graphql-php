<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class FragmentASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::FRAGMENT;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::SPREAD);

        $tokenValue = $lexer->getTokenValue();

        if ($tokenValue !== 'on' && $this->peek($lexer, TokenKindEnum::NAME)) {
            return [
                'kind'       => NodeKindEnum::FRAGMENT_SPREAD,
                'name'       => $this->parseFragmentName($lexer),
                'directives' => $this->buildAST(ASTKindEnum::DIRECTIVES, $lexer),
                'loc'        => $this->buildLocation($lexer, $start),
            ];
        }

        if ($tokenValue === 'on') {
            $lexer->advance();

            $typeCondition = $this->buildAST(ASTKindEnum::NAMED_TYPE, $lexer);
        }

        return [
            'kind'          => NodeKindEnum::INLINE_FRAGMENT,
            'typeCondition' => $typeCondition ?? null,
            'directives'    => $this->buildAST(ASTKindEnum::DIRECTIVES, $lexer),
            'selectionSet'  => $this->buildAST(ASTKindEnum::SELECTION_SET, $lexer),
            'loc'           => $this->buildLocation($lexer, $start),
        ];
    }

    /**
     * @param LexerInterface $lexer
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseFragmentName(LexerInterface $lexer): array
    {
        if ($lexer->getTokenValue() === 'on') {
            throw $this->unexpected($lexer);
        }

        return $this->buildAST(ASTKindEnum::NAME, $lexer);
    }
}
