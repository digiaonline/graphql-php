<?php

namespace Digia\GraphQL\Language\AST;

use Digia\GraphQL\ConfigObject;

class Location extends ConfigObject
{

    /**
     * @var int
     */
    protected $start;

    /**
     * @var int
     */
    protected $end;

    /**
     * @var Token
     */
    protected $startToken;

    /**
     * @var Token
     */
    protected $endToken;

    /**
     * @var Source
     */
    protected $source;

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
