<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\Node\NodeInterface;

/**
 * Tagging interface for serializable types (Enum and Scalar).
 */
interface SerializableTypeInterface extends TypeInterface
{
    /**
     * @param mixed $value
     * @return null|string
     */
    public function serialize($value);

    /**
     * @param mixed $value
     * @return mixed|null
     */
    public function parseValue($value);

    /**
     * @param NodeInterface $node
     * @return mixed|null
     */
    public function parseLiteral(NodeInterface $node);
}
