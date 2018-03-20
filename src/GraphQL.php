<?php

namespace Digia\GraphQL;

use Digia\GraphQL\Cache\CacheProvider;
use Digia\GraphQL\Execution\ExecutionProvider;
use Digia\GraphQL\Language\LanguageProvider;
use Digia\GraphQL\SchemaBuilder\SchemaBuilderProvider;
use Digia\GraphQL\SchemaValidator\SchemaValidatorProvider;
use Digia\GraphQL\Type\CoercerProvider;
use Digia\GraphQL\Type\DirectivesProvider;
use Digia\GraphQL\Type\IntrospectionProvider;
use Digia\GraphQL\Type\ScalarTypesProvider;
use Digia\GraphQL\Util\UtilityProvider;
use Digia\GraphQL\Validation\RulesProvider;
use Digia\GraphQL\Validation\ValidationProvider;
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

    public const SCHEMA_INTROSPECTION             = '__Schema';
    public const DIRECTIVE_INTROSPECTION          = '__Directive';
    public const DIRECTIVE_LOCATION_INTROSPECTION = '__DirectiveLocation';
    public const TYPE_INTROSPECTION               = '__Type';
    public const FIELD_INTROSPECTION              = '__Field';
    public const INPUT_VALUE_INTROSPECTION        = '__InputValue';
    public const ENUM_VALUE_INTROSPECTION         = '__EnumValue';
    public const TYPE_KIND_INTROSPECTION          = '__TypeKind';

    public const SCHEMA_META_FIELD_DEFINITION    = 'SchemaMetaFieldDefinition';
    public const TYPE_META_FIELD_DEFINITION      = 'TypeMetaFieldDefinition';
    public const TYPE_NAME_META_FIELD_DEFINITION = 'TypeNameMetaFieldDefinition';

    /**
     * @var array
     */
    private static $providers = [
        CacheProvider::class,
        LanguageProvider::class,
        SchemaBuilderProvider::class,
        SchemaValidatorProvider::class,
        CoercerProvider::class,
        IntrospectionProvider::class,
        ScalarTypesProvider::class,
        DirectivesProvider::class,
        RulesProvider::class,
        ValidationProvider::class,
        ExecutionProvider::class,
        UtilityProvider::class,
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
