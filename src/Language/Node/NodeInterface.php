<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;
use Digia\GraphQL\Language\Visitor\VisitorInfo;

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
     * @return string
     */
    public function __toString(): string;

    /**
     * @param VisitorInfo $visitorInfo
     * @return NodeInterface|null
     */
    public function acceptVisitor(VisitorInfo $visitorInfo): ?NodeInterface;

    /**
     * @return VisitorInfo|null
     */
    public function getVisitorInfo(): ?VisitorInfo;

    /**
     * @param NodeInterface $node
     * @return bool
     */
    public function determineIsEdited(NodeInterface $node): bool;

    /**
     * @param int $depth
     * @return NodeInterface|null
     */
    public function getAncestor(int $depth = 1): ?NodeInterface;

    /**
     * @return bool
     */
    public function isEdited(): bool;
}
