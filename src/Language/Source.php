<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\InvariantException;

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
     * @throws InvariantException
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
     * @return SourceLocation
     */
    public function getLocationOffset(): SourceLocation
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
     * @param SourceLocation|null $locationOffset
     * @return Source
     * @throws InvariantException
     */
    protected function setLocationOffset(?SourceLocation $locationOffset): Source
    {
        if (null !== $locationOffset) {
            if ($locationOffset->getLine() < 1) {
                throw new InvariantException("'line is 1-indexed and must be positive");
            }

            if ($locationOffset->getColumn() < 1) {
                throw new InvariantException("'column is 1-indexed and must be positive'");
            }
        }

        $this->locationOffset = $locationOffset ?? new SourceLocation();

        return $this;
    }
}
