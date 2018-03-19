<?php

namespace Digia\GraphQL\Type\Coercer;

use Digia\GraphQL\Language\Node\NodeInterface;

interface CoercerInterface
{
    /**
     * @param $value
     * @return mixed
     */
    public function serialize($value);

    /**
     * @param $value
     * @return mixed
     */
    public function parseValue($value);

    /**
     * @param NodeInterface $node
     * @return mixed
     */
    public function parseLiteral(NodeInterface $node);
}
