<?php

namespace Digia\GraphQL\Type\Definition\Contract;

use Digia\GraphQL\Language\AST\Node\NodeInterface;

interface TransformInterface
{

    /**
     * @param mixed $value
     * @return mixed
     */
    public function serialize($value);

    /**
     * @param $value
     * @return mixed
     */
    public function parseValue($value);

    /**
     * @param NodeInterface $astNode
     * @param array         ...$args
     * @return mixed
     */
    public function parseLiteral(NodeInterface $astNode, ...$args);
}
