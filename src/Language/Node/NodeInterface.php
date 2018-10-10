<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;
use Digia\GraphQL\Language\Visitor\VisitorBreak;
use Digia\GraphQL\Language\Visitor\VisitorInterface;

interface NodeInterface
{

    /**
     * @return string
     */
    public function getKind(): string;

    /**
     * @return bool
     */
    public function hasLocation(): bool;

    /**
     * @return Location|null
     */
    public function getLocation(): ?Location;

    /**
     * @return array
     */
    public function toAST(): array;

    /**
     * @return string
     */
    public function toJSON(): string;

    /**
     * @param VisitorInterface   $visitor
     * @param string|int         $key
     * @param NodeInterface|null $parent
     * @param array              $path
     * @param array              $ancestors
     * @return NodeInterface|null
     * @throws VisitorBreak
     */
    public function acceptVisitor(
        VisitorInterface $visitor,
        $key = null,
        ?NodeInterface $parent = null,
        array $path = [],
        array $ancestors = []
    ): ?NodeInterface;

    /**
     * @return NodeInterface|null
     */
    public function getAncestor(int $depth = 1): ?NodeInterface;

    /**
     * @param NodeInterface $node
     * @return bool
     */
    public function determineIsEdited(NodeInterface $node): bool;

    /**
     * @return bool
     */
    public function isEdited(): bool;
}
