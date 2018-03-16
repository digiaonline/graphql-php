<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\Node\NodeInterface;

interface NamedTypeInterface
{

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function __toString(): string;

    /**
     * @return NodeInterface|null
     */
    public function getAstNode(): ?NodeInterface;
}
