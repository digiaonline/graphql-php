<?php

namespace Digia\GraphQL\Language\AST\Builder\Contract;

use Digia\GraphQL\Language\Location;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

interface NodeFactoryInterface
{

    /**
     * @param array $ast
     * @return NodeInterface|null
     */
    public function build(array $ast): ?NodeInterface;

    /**
     * @param array $ast
     * @return Location
     */
    public function createLocation(array $ast): Location;
}
