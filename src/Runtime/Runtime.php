<?php

namespace Digia\GraphQL;

use League\Container\Container;
use Psr\Container\ContainerInterface;

class GraphQL
{

    /**
     * @var array
     */
    protected $config;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * GraphQL constructor.
     *
     * @param array                   $config
     * @param null|ContainerInterface $container
     */
    public function __construct(array $config = [], ?ContainerInterface $container = null)
    {
        $this->config    = $config;
        $this->container = $container ?: new Container();
    }

}
