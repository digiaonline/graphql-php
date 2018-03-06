<?php

namespace Digia\GraphQL\Language\Reader;

use Digia\GraphQL\Language\LexerInterface;

abstract class AbstractReader implements ReaderInterface
{

    /**
     * @var LexerInterface
     */
    protected $lexer;

    /**
     * @param LexerInterface $lexer
     */
    public function setLexer(LexerInterface $lexer): void
    {
        $this->lexer = $lexer;
    }
}
