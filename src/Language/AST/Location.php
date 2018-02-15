<?php

namespace Digia\GraphQL\Language\AST;

use Digia\GraphQL\ConfigObject;

class Location extends ConfigObject
{

    /**
     * @var int
     */
    private $start;

    /**
     * @var int
     */
    private $end;

    /**
     * @var Token
     */
    private $startToken;

    /**
     * @var Token
     */
    private $endToken;

    /**
     * @var Source
     */
    private $source;

    /**
     * @return int
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * @return int
     */
    public function getEnd(): int
    {
        return $this->end;
    }

    /**
     * @return Token
     */
    public function getStartToken(): Token
    {
        return $this->startToken;
    }

    /**
     * @return Token
     */
    public function getEndToken(): Token
    {
        return $this->endToken;
    }

    /**
     * @return Source
     */
    public function getSource(): Source
    {
        return $this->source;
    }
}
