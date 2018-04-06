<?php

namespace Digia\GraphQL\Language;

interface TokenReaderInterface
{

    /**
     * @param int $code
     * @param int $pos
     * @param int $line
     * @param int $col
     * @param Token $prev
     *
     * @return Token
     */
    public function read(
      int $code,
      int $pos,
      int $line,
      int $col,
      Token $prev
    ): Token;

    /**
     * @param \Digia\GraphQL\Language\LexerInterface $lexer
     *
     * @return $this
     */
    public function setLexer(LexerInterface $lexer);

    /**
     * @return \Digia\GraphQL\Language\LexerInterface
     */
    public function getLexer(): LexerInterface;

}
