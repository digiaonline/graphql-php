<?php

namespace Digia\GraphQL\Language\Reader;

use Digia\GraphQL\Language\Token;

interface ReaderInterface
{

    /**
     * @param int   $code
     * @param int   $pos
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     */
    public function read(int $code, int $pos, int $line, int $col, Token $prev): Token;

    /**
     * @param int $code
     * @param int $pos
     * @return bool
     */
    public function supportsReader(int $code, int $pos): bool;
}
