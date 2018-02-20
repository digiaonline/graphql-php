<?php

namespace Digia\GraphQL\Language;

class Location
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
     * @var ?Source
     */
    protected $source;

    /**
     * Location constructor.
     *
     * @param Token       $startToken
     * @param Token       $endToken
     * @param Source|null $source
     */
    public function __construct(Token $startToken, Token $endToken, ?Source $source = null)
    {
        $this->start      = $startToken->getStart();
        $this->end        = $endToken->getEnd();
        $this->startToken = $startToken;
        $this->endToken   = $endToken;
        $this->source     = $source;
    }

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
    public function getSource(): ?Source
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function toJSON(): string
    {
        return json_encode([
            'start' => $this->start,
            'end'   => $this->end,
        ]);
    }
}
