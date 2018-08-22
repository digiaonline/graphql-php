<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Util\ArrayToJsonTrait;
use Digia\GraphQL\Util\SerializationInterface;

class Location implements SerializationInterface
{
    use ArrayToJsonTrait;

    /**
     * @var int
     */
    protected $start;

    /**
     * @var int
     */
    protected $end;

    /**
     * @var Source|null
     */
    protected $source;

    /**
     * Location constructor.
     *
     * @param int         $start
     * @param int         $end
     * @param Source|null $source
     */
    public function __construct(int $start, int $end, ?Source $source = null)
    {
        $this->start  = $start;
        $this->end    = $end;
        $this->source = $source;
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
     * @return bool
     */
    public function hasSource(): bool
    {
        return null !== $this->source;
    }

    /**
     * @return Source|null
     */
    public function getSource(): ?Source
    {
        return $this->source;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'start'  => $this->start,
            'end'    => $this->end,
        ];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJSON();
    }
}
