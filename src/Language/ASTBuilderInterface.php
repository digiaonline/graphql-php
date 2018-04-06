<?php

namespace Digia\GraphQL\Language;

interface ASTBuilderInterface
{

    /**
     * @param LexerInterface $lexer
     *
     * @return array|null
     */
    public function buildDocument(LexerInterface $lexer): ?array;

    /**
     * @param LexerInterface $lexer
     * @param bool $isConst
     *
     * @return mixed
     */
    public function buildValueLiteral(
        LexerInterface $lexer,
        bool $isConst = false
    ): ?array;

    /**
     * @param LexerInterface $lexer
     *
     * @return array|null
     */
    public function buildTypeReference(LexerInterface $lexer): ?array;
}
