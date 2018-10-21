<?php

namespace Digia\GraphQL\Language\Visitor;

use Digia\GraphQL\Language\Node\NodeInterface;

class VisitorInfo
{
    /**
     * @var VisitorInterface
     */
    protected $visitor;

    /**
     * @var string|int|null
     */
    protected $key;

    /**
     * @var NodeInterface|null
     */
    protected $parent;

    /**
     * @var array
     */
    protected $path;

    /**
     * @var array
     */
    protected $ancestors;

    /**
     * VisitorInfo constructor.
     * @param VisitorInterface   $visitor
     * @param int|null|string    $key
     * @param NodeInterface|null $parent
     * @param array              $path
     * @param array              $ancestors
     */
    public function __construct(
        VisitorInterface $visitor,
        $key = null,
        ?NodeInterface $parent = null,
        array $path = [],
        array $ancestors = []
    ) {
        $this->visitor   = $visitor;
        $this->key       = $key;
        $this->parent    = $parent;
        $this->path      = $path;
        $this->ancestors = $ancestors;
    }

    /**
     * Appends a key to the path.
     * @param string $key
     */
    public function addOneToPath(string $key)
    {
        $this->path[] = $key;
    }

    /**
     * Removes the last item from the path.
     */
    public function removeOneFromPath()
    {
        $this->path = \array_slice($this->path, 0, -1);
    }

    /**
     * Adds an ancestor.
     * @param NodeInterface $node
     */
    public function addAncestor(NodeInterface $node)
    {
        $this->ancestors[] = $node;
    }

    /**
     *  Removes the last ancestor.
     */
    public function removeAncestor()
    {
        $this->ancestors = \array_slice($this->ancestors, 0, -1);
    }

    /**
     * @inheritdoc
     */
    public function getAncestor(int $depth = 1): ?NodeInterface
    {
        if (empty($this->ancestors)) {
            return null;
        }

        $index = \count($this->ancestors) - $depth;

        return $this->ancestors[$index] ?? null;
    }

    /**
     * @return VisitorInterface
     */
    public function getVisitor(): VisitorInterface
    {
        return $this->visitor;
    }

    /**
     * @return int|null|string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return NodeInterface|null
     */
    public function getParent(): ?NodeInterface
    {
        return $this->parent;
    }

    /**
     * @return array
     */
    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getAncestors(): array
    {
        return $this->ancestors;
    }
}
