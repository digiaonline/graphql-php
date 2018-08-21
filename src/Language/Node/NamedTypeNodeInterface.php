<?php

namespace Digia\GraphQL\Language\Node;

/**
 * Interface for named type nodes.
 */
interface NamedTypeNodeInterface extends TypeNodeInterface
{
    /**
     * @return NameNode|null
     */
    public function getName(): ?NameNode;

    /**
     * @return null|string
     */
    public function getNameValue(): ?string;
}
