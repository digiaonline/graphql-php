<?php

namespace Digia\GraphQL;

use Digia\GraphQL\Provider\CacheProvider;
use Digia\GraphQL\Provider\DirectivesProvider;
use Digia\GraphQL\Provider\ParserProvider;
use Digia\GraphQL\Provider\PrinterProvider;
use Digia\GraphQL\Provider\ScalarTypesProvider;
use Digia\GraphQL\Provider\SchemaBuilderProvider;
use League\Container\Container;
use League\Container\ContainerInterface;

class GraphQL
{

    public const BOOLEAN = 'GraphQLBoolean';
    public const FLOAT   = 'GraphQLFloat';
    public const INT     = 'GraphQLInt';
    public const ID      = 'GraphQLID';
    public const STRING  = 'GraphQLString';

    public const DEPRECATED_DIRECTIVE = 'GraphQLDeprecatedDirective';
    public const INCLUDE_DIRECTIVE    = 'GraphQLIncludeDirective';
    public const SKIP_DIRECTIVE       = 'GraphQLSkipDirective';

    /**
     * @var array
     */
    private static $providers = [
        CacheProvider::class,
        ParserProvider::class,
        PrinterProvider::class,
        SchemaBuilderProvider::class,
        ScalarTypesProvider::class,
        DirectivesProvider::class,
    ];

    /**
     * @var GraphQL
     */
    private static $instance;

    /**
     * @var Container
     */
    protected $container;

    /**
     * GraphQL constructor.
     */
    private function __construct()
    {
        $container = new Container();

        $this->registerProviders($container);

        $this->container = $container;
    }

    /**
     * Registers the service provides with the container.
     */
    protected function registerProviders(ContainerInterface $container): void
    {
        foreach (self::$providers as $className) {
            $container->addServiceProvider($className);
        }
    }

    /**
     * @return GraphQL
     */
    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * @param string $id
     * @param array  $args
     * @return mixed
     */
    public static function get(string $id, array $args = [])
    {
        return self::getInstance()->getContainer()->get($id, $args);
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
}
