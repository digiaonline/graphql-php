<?php

namespace Digia\GraphQL\Language\Visitor;

use Digia\GraphQL\Language\Node\NodeInterface;

interface AcceptsVisitorsInterface
{
    /**
     * @param VisitorInterface   $visitor
     * @param mixed              $key
     * @param NodeInterface|null $parent
     * @param string[]           $path
     * @param NodeInterface[]    $ancestors
     * @return NodeInterface|null
     */
    public function acceptVisitor(
        VisitorInterface $visitor,
        $key = null,
        ?NodeInterface $parent = null,
        array $path = [],
        array $ancestors = []
    ): ?NodeInterface;

    /**
     * @param NodeInterface|AcceptsVisitorsTrait $node
     * @return bool
     */
    public function determineIsEdited($node): bool;

    /**
     * @return mixed
     */
    public function getKey();

    /**
     * @return NodeInterface|null
     */
    public function getParent(): ?NodeInterface;

    /**
     * @return string[]
     */
    public function getPath(): array;

    /**
     * @param int $depth
     * @return NodeInterface|null
     */
    public function getAncestor(int $depth = 1): ?NodeInterface;

    /**
     * @return NodeInterface[]
     */
    public function getAncestors(): array;

    public function setVisitor(VisitorInterface $visitor);

    public function setKey($key);

    public function setParent(?NodeInterface $parent);

    public function setPath(array $path);

    public function setAncestors(array $ancestors);

    public function setIsEdited(bool $isEdited);
}
