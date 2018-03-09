<?php

namespace Digia\GraphQL\Error;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\Source;

/**
 * An ExecutionException describes an exception thrown during the execute
 * phase of performing a GraphQL operation. In addition to a message
 * and stack trace, it also includes information about the locations in a
 * GraphQL document and/or execution result that correspond to the Error.
 */
class ExecutionException extends AbstractException
{

    /**
     * @var string[]
     */
    protected $locations;

    /**
     * @var string[]
     */
    protected $path;

    /**
     * @var NodeInterface[]
     */
    protected $nodes;

    /**
     * @var Source|null
     */
    protected $source;

    /**
     * @var int[]
     */
    protected $positions;

    /**
     * @var array
     */
    protected $extensions;

    /**
     * ExecutionException constructor.
     *
     * @param string      $message
     * @param array|null  $nodes
     * @param Source|null $source
     * @param array|null  $positions
     * @param array|null  $path
     * @param array|null  $extensions
     */
    public function __construct(
        string $message,
        ?array $nodes = null,
        ?Source $source = null,
        ?array $positions = null,
        ?array $path = null,
        ?array $extensions = null
    ) {
        parent::__construct($message);

        $this->nodes      = $nodes;
        $this->source     = $source;
        $this->positions  = $positions;
        $this->path       = $path;
        $this->extensions = $extensions;
    }

    // TODO: Implement the rest of this class.
}
