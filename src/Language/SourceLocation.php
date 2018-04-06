<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Util\ArrayToJsonTrait;
use Digia\GraphQL\Util\SerializationInterface;

class SourceLocation implements SerializationInterface
{

    use ArrayToJsonTrait;

    /**
     * @var int
     */
    protected $line;

    /**
     * @var int
     */
    protected $column;

    /**
     * SourceLocation constructor.
     *
     * @param int $line
     * @param int $column
     */
    public function __construct(int $line = 1, int $column = 1)
    {
        $this->line = $line;
        $this->column = $column;
    }

    /**
     * @param Source $source
     * @param int $position
     *
     * @return SourceLocation
     */
    public static function fromSource(Source $source, int $position): self
    {
        $line = 1;
        $column = $position + 1;
        $matches = [];
        preg_match_all("/\r\n|[\n\r]/",
            mb_substr($source->getBody(), 0, $position), $matches,
            PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $index => $match) {
            $line += 1;
            $column = $position + 1 - ($match[1] + mb_strlen($match[0]));
        }

        return new static($line, $column);
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @return int
     */
    public function getColumn(): int
    {
        return $this->column;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'line' => $this->line,
            'column' => $this->column,
        ];
    }
}
