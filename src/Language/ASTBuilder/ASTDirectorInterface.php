<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\LexerInterface;

interface ASTDirectorInterface
{
    /**
     * @param string         $kind
     * @param LexerInterface $lexer
     * @param array          $params
     * @return array|null
     */
    public function build(string $kind, LexerInterface $lexer, array $params = []): ?array;
}
