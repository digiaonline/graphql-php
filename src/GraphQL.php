<?php

namespace Digia\GraphQL;

use Digia\GraphQL\Provider\CacheProvider;
use Digia\GraphQL\Provider\DirectivesProvider;
use Digia\GraphQL\Provider\ExecutionProvider;
use Digia\GraphQL\Provider\IntrospectionTypesProvider;
use Digia\GraphQL\Provider\ParserProvider;
use Digia\GraphQL\Provider\PrinterProvider;
use Digia\GraphQL\Provider\ScalarTypesProvider;
use Digia\GraphQL\Provider\SchemaBuilderProvider;
use Digia\GraphQL\Provider\ValidationProvider;
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

    public const INTROSPECTION_SCHEMA             = '__Schema';
    public const INTROSPECTION_DIRECTIVE          = '__Directive';
    public const INTROSPECTION_DIRECTIVE_LOCATION = '__DirectiveLocation';
    public const INTROSPECTION_TYPE               = '__Type';
    public const INTROSPECTION_FIELD              = '__Field';
    public const INTROSPECTION_INPUT_VALUE        = '__InputValue';
    public const INTROSPECTION_ENUM_VALUE         = '__EnumValue';
    public const INTROSPECTION_TYPE_KIND          = '__TypeKind';

    /**
     * @var array
     */
    private static $providers = [
        CacheProvider::class,
        ParserProvider::class,
        PrinterProvider::class,
        SchemaBuilderProvider::class,
        IntrospectionTypesProvider::class,
        ScalarTypesProvider::class,
        DirectivesProvider::class,
        ValidationProvider::class,
        ExecutionProvider::class,
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
