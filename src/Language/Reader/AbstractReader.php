<?php

namespace Digia\GraphQL\Language\Reader;

use Digia\GraphQL\Language\Lexer;
use Digia\GraphQL\Language\LexerInterface;

abstract class AbstractReader implements ReaderInterface
{

    /**
     * @var Lexer
     */
    protected $lexer;

    /**
     * @param Lexer $lexer
     */
    public function setLexer(LexerInterface $lexer): void
    {
        $this->lexer = $lexer;
    }
}
