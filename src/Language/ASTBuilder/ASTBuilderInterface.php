<?php

namespace Digia\GraphQL\Language\ASTBuilder;

use Digia\GraphQL\Language\LexerInterface;

interface ASTBuilderInterface
{
    /**
     * @param string $kind
     * @return bool
     */
    public function supportsBuilder(string $kind): bool;

    /**
     * @param LexerInterface $lexer
     * @param array          $params
     * @return array
     */
    public function build(LexerInterface $lexer, array $params): ?array;

    /**
     * @param ASTDirectorInterface $director
     */
    public function setDirector(ASTDirectorInterface $director);
}
