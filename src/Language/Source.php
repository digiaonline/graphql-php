<?php

namespace Digia\GraphQL\Language;

use function Digia\GraphQL\Util\invariant;

/**
 * A representation of source input to GraphQL.
 * `name` and `locationOffset` are optional. They are useful for clients who
 * store GraphQL documents in source files; for example, if the GraphQL input
 * starts at line 40 in a file named Foo.graphql, it might be useful for name to
 * be "Foo.graphql", line to be 40 and column to be 0.
 * line and column are 1-indexed
 */
class Source
{

    /**
     * @var string
     */
    protected $body;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var SourceLocation|null
     */
    protected $locationOffset;

    /**
     * Source constructor.
     *
     * @param string              $body
     * @param null|string         $name
     * @param SourceLocation|null $locationOffset
     * @throws \Exception
     */
    public function __construct(string $body, ?string $name = 'GraphQL request', ?SourceLocation $locationOffset = null)
    {
        $this->body = $body;
        $this->name = $name;

        $this->setLocationOffset($locationOffset);
    }

    /**
     * @return int
     */
    public function getBodyLength(): int
    {
        return mb_strlen($this->body);
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getLocationOffset(): ?SourceLocation
    {
        return $this->locationOffset;
    }

    /**
     * @param string $body
     * @return Source
     */
    protected function setBody(string $body): Source
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @param string $name
     * @return Source
     */
    protected function setName(string $name): Source
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param int $line
     * @return Source
     * @throws \Exception
     */
    protected function setLocationOffset(?SourceLocation $locationOffset): Source
    {
        if (null !== $locationOffset) {
            invariant(
                $locationOffset->getLine() > 0,
                'line is 1-indexed and must be positive'
            );

            invariant(
                $locationOffset->getColumn() > 0,
                'column is 1-indexed and must be positive'
            );
        }

        $this->locationOffset = $locationOffset;
        return $this;
    }
}
