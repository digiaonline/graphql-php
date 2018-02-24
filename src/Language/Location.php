<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Contract\SerializationInterface;
use function Digia\GraphQL\Util\jsonEncode;

class Location implements SerializationInterface
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
            'start' => $this->start,
            'end'   => $this->end,
        ];
    }

    /**
     * @return string
     */
    public function toJSON(): string
    {
        return jsonEncode($this->toArray());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJSON();
    }
}
