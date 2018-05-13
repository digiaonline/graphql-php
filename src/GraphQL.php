<?php

namespace Digia\GraphQL;

use Digia\GraphQL\Execution\ExecutionInterface;
use Digia\GraphQL\Execution\ExecutionProvider;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Language\LanguageProvider;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\TypeNodeInterface;
use Digia\GraphQL\Language\Node\ValueNodeInterface;
use Digia\GraphQL\Language\NodePrinterInterface;
use Digia\GraphQL\Language\ParserInterface;
use Digia\GraphQL\Language\Source;
use Digia\GraphQL\Schema\Building\SchemaBuilderInterface;
use Digia\GraphQL\Schema\Building\SchemaBuildingProvider;
use Digia\GraphQL\Schema\Extension\SchemaExtenderInterface;
use Digia\GraphQL\Schema\Extension\SchemaExtensionProvider;
use Digia\GraphQL\Schema\Resolver\ResolverRegistry;
use Digia\GraphQL\Schema\Resolver\ResolverRegistryInterface;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Schema\Validation\SchemaValidationProvider;
use Digia\GraphQL\Schema\Validation\SchemaValidatorInterface;
use Digia\GraphQL\Type\CoercerProvider;
use Digia\GraphQL\Type\DirectivesProvider;
use Digia\GraphQL\Type\IntrospectionProvider;
use Digia\GraphQL\Type\ScalarTypesProvider;
use Digia\GraphQL\Util\UtilityProvider;
use Digia\GraphQL\Validation\RulesProvider;
use Digia\GraphQL\Validation\ValidationProvider;
use Digia\GraphQL\Validation\ValidatorInterface;
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
        LanguageProvider::class,
        SchemaBuildingProvider::class,
        SchemaExtensionProvider::class,
        SchemaValidationProvider::class,
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
     * @return GraphQL
     */
    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $id
     * @param array  $args
     * @return mixed
     */
    public static function make(string $id, array $args = [])
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return static::getInstance()
            ->getContainer()
            ->get($id, $args);
    }

    /**
     * @param string|Source                   $source
     * @param array|ResolverRegistryInterface $resolverRegistry
     * @param array                           $options
     * @return Schema
     */
    public static function buildSchema($source, $resolverRegistry, array $options = []): Schema
    {
        return static::make(SchemaBuilderInterface::class)
            ->build(
                static::parse($source, $options),
                $resolverRegistry instanceof ResolverRegistryInterface
                    ? $resolverRegistry
                    : new ResolverRegistry($resolverRegistry),
                $options
            );
    }

    /**
     * @param Schema                          $schema
     * @param string|Source                   $source
     * @param array|ResolverRegistryInterface $resolverRegistry
     * @param array                           $options
     * @return Schema
     */
    public static function extendSchema(
        Schema $schema,
        $source,
        $resolverRegistry,
        array $options = []
    ): Schema {
        return static::make(SchemaExtenderInterface::class)
            ->extend(
                $schema,
                static::parse($source, $options),
                $resolverRegistry instanceof ResolverRegistryInterface
                    ? $resolverRegistry
                    : new ResolverRegistry($resolverRegistry),
                $options
            );
    }

    /**
     * @param Schema $schema
     * @return array
     */
    public static function validateSchema(Schema $schema): array
    {
        return static::make(SchemaValidatorInterface::class)->validate($schema);
    }

    /**
     * @param string|Source $source
     * @param array         $options
     * @return DocumentNode
     * @throws Error\InvariantException
     */
    public static function parse($source, array $options = []): DocumentNode
    {
        return static::make(ParserInterface::class)->parse($source, $options);
    }

    /**
     * @param string|Source $source
     * @param array         $options
     * @return ValueNodeInterface
     */
    public static function parseValue($source, array $options = []): ValueNodeInterface
    {
        return static::make(ParserInterface::class)->parseValue($source, $options);
    }

    /**
     * @param string|Source $source
     * @param array         $options
     * @return TypeNodeInterface
     */
    public static function parseType($source, array $options = []): TypeNodeInterface
    {
        return static::make(ParserInterface::class)->parseType($source, $options);
    }

    /**
     * @param Schema       $schema
     * @param DocumentNode $document
     * @return array
     */
    public static function validate(Schema $schema, DocumentNode $document): array
    {
        return static::make(ValidatorInterface::class)->validate($schema, $document);
    }

    /**
     * @param Schema        $schema
     * @param DocumentNode  $document
     * @param mixed         $rootValue
     * @param mixed         $contextValue
     * @param array         $variableValues
     * @param string|null   $operationName
     * @param callable|null $fieldResolver
     * @return ExecutionResult
     */
    public static function execute(
        Schema $schema,
        DocumentNode $document,
        $rootValue = null,
        $contextValue = null,
        array $variableValues = [],
        $operationName = null,
        callable $fieldResolver = null
    ): ExecutionResult {
        return static::make(ExecutionInterface::class)
            ->execute(
                $schema,
                $document,
                $rootValue,
                $contextValue,
                $variableValues,
                $operationName,
                $fieldResolver
            );
    }

    /**
     * @param NodeInterface $node
     * @return string
     */
    public static function print(NodeInterface $node): string
    {
        return static::make(NodePrinterInterface::class)->print($node);
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Registers the service provides with the container.
     *
     * @param ContainerInterface $container
     */
    protected function registerProviders(ContainerInterface $container): void
    {
        foreach (self::$providers as $className) {
            $container->addServiceProvider($className);
        }
    }
}
