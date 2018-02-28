<?php

namespace Digia\GraphQL\Language\Reader;

use Digia\GraphQL\Language\Lexer;

abstract class AbstractReader implements ReaderInterface
{

    /**
     * @var Lexer
     */
    protected $lexer;

    /**
     * @param Lexer $lexer
     */
    public function setLexer(Lexer $lexer): void
    {
        $this->lexer = $lexer;
    }
}
