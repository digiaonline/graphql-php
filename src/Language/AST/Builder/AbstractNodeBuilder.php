<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Builder\Contract\NodeBuilderInterface;
use Digia\GraphQL\Language\AST\Builder\Contract\NodeFactoryInterface;

abstract class AbstractNodeBuilder implements NodeBuilderInterface
{

    /**
     * @var NodeFactoryInterface
     */
    protected $factory;

    /**
     * @return NodeFactoryInterface
     */
    public function getFactory(): NodeFactoryInterface
    {
        return $this->factory;
    }

    /**
     * @param NodeFactoryInterface $factory
     * @return $this
     */
    public function setFactory(NodeFactoryInterface $factory)
    {
        $this->factory = $factory;
        return $this;
    }
}
