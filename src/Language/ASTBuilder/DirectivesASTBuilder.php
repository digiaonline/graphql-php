<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\TokenKindEnum;

class DirectivesASTBuilder extends AbstractASTBuilder
{
    /**
     * @inheritdoc
     */
    public function supportsBuilder(string $kind): bool
    {
        return $kind === ASTKindEnum::DIRECTIVES;
    }

    /**
     * @inheritdoc
     */
    public function build(LexerInterface $lexer, array $params): ?array
    {
        $isConst = $params['isConst'] ?? false;

        $directives = [];

        while ($this->peek($lexer, TokenKindEnum::AT)) {
            $directives[] = $this->parseDirective($lexer, $isConst);
        }

        return $directives;
    }

    /**
     * @param LexerInterface $lexer
     * @param bool           $isConst
     * @return array
     * @throws SyntaxErrorException
     */
    protected function parseDirective(LexerInterface $lexer, bool $isConst): array
    {
        $start = $lexer->getToken();

        $this->expect($lexer, TokenKindEnum::AT);

        return [
            'kind'      => NodeKindEnum::DIRECTIVE,
            'name'      => $this->buildAST(ASTKindEnum::NAME, $lexer),
            'arguments' => $this->buildAST(ASTKindEnum::ARGUMENTS, $lexer),
            'loc'       => $this->buildLocation($lexer, $start),
        ];
    }
}
