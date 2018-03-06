<?php

namespace Digia\GraphQL;

use Digia\GraphQL\Cache\RuntimeCache;
use Digia\GraphQL\Language\AST\Builder\ArgumentBuilder;
use Digia\GraphQL\Language\AST\Builder\BooleanBuilder;
use Digia\GraphQL\Language\AST\Builder\BuilderInterface;
use Digia\GraphQL\Language\AST\Builder\DirectiveBuilder;
use Digia\GraphQL\Language\AST\Builder\DirectiveDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\DocumentBuilder;
use Digia\GraphQL\Language\AST\Builder\EnumBuilder;
use Digia\GraphQL\Language\AST\Builder\EnumTypeDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\EnumTypeExtensionBuilder;
use Digia\GraphQL\Language\AST\Builder\EnumValueDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\FieldBuilder;
use Digia\GraphQL\Language\AST\Builder\FieldDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\FloatBuilder;
use Digia\GraphQL\Language\AST\Builder\FragmentDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\FragmentSpreadBuilder;
use Digia\GraphQL\Language\AST\Builder\InlineFragmentBuilder;
use Digia\GraphQL\Language\AST\Builder\InputObjectTypeDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\InputObjectTypeExtensionBuilder;
use Digia\GraphQL\Language\AST\Builder\InputValueDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\IntBuilder;
use Digia\GraphQL\Language\AST\Builder\InterfaceTypeDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\InterfaceTypeExtensionBuilder;
use Digia\GraphQL\Language\AST\Builder\ListBuilder;
use Digia\GraphQL\Language\AST\Builder\ListTypeBuilder;
use Digia\GraphQL\Language\AST\Builder\NameBuilder;
use Digia\GraphQL\Language\AST\Builder\NamedTypeBuilder;
use Digia\GraphQL\Language\AST\Builder\NodeBuilder;
use Digia\GraphQL\Language\AST\Builder\NodeBuilderInterface;
use Digia\GraphQL\Language\AST\Builder\NonNullTypeBuilder;
use Digia\GraphQL\Language\AST\Builder\NullBuilder;
use Digia\GraphQL\Language\AST\Builder\ObjectBuilder;
use Digia\GraphQL\Language\AST\Builder\ObjectFieldBuilder;
use Digia\GraphQL\Language\AST\Builder\ObjectTypeDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\ObjectTypeExtensionBuilder;
use Digia\GraphQL\Language\AST\Builder\OperationDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\OperationTypeDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\ScalarTypeDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\ScalarTypeExtensionBuilder;
use Digia\GraphQL\Language\AST\Builder\SchemaDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\SelectionSetBuilder;
use Digia\GraphQL\Language\AST\Builder\StringBuilder;
use Digia\GraphQL\Language\AST\Builder\UnionTypeDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\UnionTypeExtensionBuilder;
use Digia\GraphQL\Language\AST\Builder\VariableBuilder;
use Digia\GraphQL\Language\AST\Builder\VariableDefinitionBuilder;
use Digia\GraphQL\Language\AST\DirectiveLocationEnum;
use Digia\GraphQL\Language\AST\Node\BooleanValueNode;
use Digia\GraphQL\Language\AST\Node\FloatValueNode;
use Digia\GraphQL\Language\AST\Node\IntValueNode;
use Digia\GraphQL\Language\AST\Node\NamedTypeNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\StringValueNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Schema\DefinitionBuilder;
use Digia\GraphQL\Language\AST\Schema\DefinitionBuilderInterface;
use Digia\GraphQL\Language\AST\Schema\SchemaBuilder;
use Digia\GraphQL\Language\AST\Schema\SchemaBuilderInterface;
use Digia\GraphQL\Language\Lexer;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Parser;
use Digia\GraphQL\Language\ParserInterface;
use Digia\GraphQL\Language\Printer;
use Digia\GraphQL\Language\PrinterInterface;
use Digia\GraphQL\Language\Reader\AmpReader;
use Digia\GraphQL\Language\Reader\AtReader;
use Digia\GraphQL\Language\Reader\BangReader;
use Digia\GraphQL\Language\Reader\BlockStringReader;
use Digia\GraphQL\Language\Reader\BraceReader;
use Digia\GraphQL\Language\Reader\BracketReader;
use Digia\GraphQL\Language\Reader\ColonReader;
use Digia\GraphQL\Language\Reader\CommentReader;
use Digia\GraphQL\Language\Reader\DollarReader;
use Digia\GraphQL\Language\Reader\EqualsReader;
use Digia\GraphQL\Language\Reader\NameReader;
use Digia\GraphQL\Language\Reader\NumberReader;
use Digia\GraphQL\Language\Reader\ParenthesisReader;
use Digia\GraphQL\Language\Reader\PipeReader;
use Digia\GraphQL\Language\Reader\ReaderInterface;
use Digia\GraphQL\Language\Reader\SpreadReader;
use Digia\GraphQL\Language\Reader\StringReader;
use Digia\GraphQL\Type\Definition\TypeNameEnum;
use League\Container\Container;
use League\Container\Definition\DefinitionInterface;
use Psr\SimpleCache\CacheInterface;
use const Digia\GraphQL\Type\MAX_INT;
use const Digia\GraphQL\Type\MIN_INT;
use function Digia\GraphQL\Type\GraphQLBoolean;
use function Digia\GraphQL\Type\GraphQLDirective;
use function Digia\GraphQL\Type\GraphQLNonNull;
use function Digia\GraphQL\Type\GraphQLScalarType;
use function Digia\GraphQL\Type\GraphQLString;

class GraphQL
{

    /**
     * @var GraphQL
     */
    private static $instance;

    /**
     * @var BuilderInterface[]
     */
    private static $nodeBuilders;

    /**
     * @var ReaderInterface[]
     */
    private static $sourceReaders;

    /**
     * @var Container
     */
    protected $container;

    /**
     * GraphQL constructor.
     */
    private function __construct()
    {
        $this->container = new Container();

        $this->registerBindings();
    }

    /**
     * @return GraphQL
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * @return BuilderInterface[]
     */
    public static function getNodeBuilders(): array
    {
        if (null === self::$nodeBuilders) {
            foreach (self::$supportedNodeBuilders as $className) {
                self::$nodeBuilders[] = new $className();
            }
        }

        return self::$nodeBuilders;
    }

    /**
     * @return ReaderInterface[]
     */
    public static function getSourceReaders(): array
    {
        if (null === self::$sourceReaders) {
            foreach (self::$supportedSourceReaders as $className) {
                self::$sourceReaders[] = new $className();
            }
        }

        return self::$sourceReaders;
    }

    /**
     * @param string $id
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public static function get(string $id)
    {
        return self::getInstance()->getContainer()->get($id);
    }

    /**
     * @param string $id
     * @param mixed  $concrete
     * @return DefinitionInterface
     */
    public function bind(string $id, $concrete): DefinitionInterface
    {
        return $this->container->add($id, $concrete);
    }

    /**
     * @param string $id
     * @param mixed  $concrete
     * @return DefinitionInterface
     */
    public function shared(string $id, $concrete): DefinitionInterface
    {
        return $this->container->add($id, $concrete, true);
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     *
     */
    protected function registerBindings()
    {
        $this->registerCache();
        $this->registerParser();
        $this->registerPrinter();
        $this->registerSchemaBuilder();
        $this->registerScalarTypes();
        $this->registerDirectives();
    }

    /**
     * Registers the cache with the container.
     */
    protected function registerCache()
    {
        $this->bind(CacheInterface::class, RuntimeCache::class);
    }

    /**
     * Registers the GraphQL parser with the container.
     */
    protected function registerParser()
    {
        $this->shared(NodeBuilderInterface::class, function () {
            return new NodeBuilder(self::getNodeBuilders());
        });

        $this->shared(ParserInterface::class, Parser::class)
            ->withArgument(NodeBuilderInterface::class);

        $this->bind(LexerInterface::class, function () {
            return new Lexer(self::getSourceReaders());
        });
    }

    /**
     * Registers the GraphQL printer with the container.
     */
    protected function registerPrinter()
    {
        $this->shared(PrinterInterface::class, Printer::class);
    }

    /**
     * @throws \Exception
     */
    protected function registerSchemaBuilder()
    {
        $this->shared(DefinitionBuilderInterface::class, DefinitionBuilder::class)
            ->withArguments([
                function (NamedTypeNode $node) {
                    throw new \Exception(sprintf('Type "%s" not found in document.', $node->getNameValue()));
                },
                CacheInterface::class,
            ]);

        $this->shared(SchemaBuilderInterface::class, SchemaBuilder::class)
            ->withArgument(DefinitionBuilderInterface::class);
    }

    /**
     * Registers the GraphQL scalar types with the container.
     * @throws \TypeError
     */
    protected function registerScalarTypes()
    {
        /**
         * @param $value
         * @return bool
         * @throws \TypeError
         */
        function coerceBoolean($value): bool
        {
            if (!is_scalar($value)) {
                throw new \TypeError(sprintf('Boolean cannot represent a non-scalar value: %s', $value));
            }

            return (bool)$value;
        }

        $this->shared('GraphQLBoolean', function () {
            return GraphQLScalarType([
                'name'        => TypeNameEnum::BOOLEAN,
                'description' => 'The `Boolean` scalar type represents `true` or `false`.',
                'serialize'   => function ($value) {
                    return coerceBoolean($value);
                },
                'parseValue'  => function ($value) {
                    return coerceBoolean($value);
                },

                'parseLiteral' => function (NodeInterface $astNode) {
                    /** @var BooleanValueNode $astNode */
                    return $astNode->getKind() === NodeKindEnum::BOOLEAN ? $astNode->getValue() : null;
                },
            ]);
        });

        /**
         * @param $value
         * @return float
         * @throws \TypeError
         */
        function coerceFloat($value): float
        {
            if ($value === '') {
                throw new \TypeError('Float cannot represent non numeric value: (empty string)');
            }

            if (is_numeric($value) || \is_bool($value)) {
                return (float)$value;
            }

            throw new \TypeError(sprintf('Float cannot represent non numeric value: %s', $value));
        }

        $this->shared('GraphQLFloat', function () {
            return GraphQLScalarType([
                'name'         => TypeNameEnum::FLOAT,
                'description'  =>
                    'The `Float` scalar type represents signed double-precision fractional ' .
                    'values as specified by ' .
                    '[IEEE 754](http://en.wikipedia.org/wiki/IEEE_floating_point).',
                'serialize'    => function ($value) {
                    return coerceFloat($value);
                },
                'parseValue'   => function ($value) {
                    return coerceFloat($value);
                },
                'parseLiteral' => function (NodeInterface $astNode) {
                    /** @var FloatValueNode $astNode */
                    return in_array($astNode->getKind(), [NodeKindEnum::FLOAT, NodeKindEnum::INT], true)
                        ? $astNode->getValue()
                        : null;
                },
            ]);
        });

        /**
         * @param $value
         * @return int
         * @throws \TypeError
         */
        function coerceInt($value)
        {
            if ($value === '') {
                throw new \TypeError('Int cannot represent non 32-bit signed integer value: (empty string)');
            }

            if (\is_bool($value)) {
                $value = (int)$value;
            }

            if (!\is_int($value) || $value > MAX_INT || $value < MIN_INT) {
                throw new \TypeError(sprintf('Int cannot represent non 32-bit signed integer value: %s', $value));
            }

            $intValue   = (int)$value;
            $floatValue = (float)$value;

            if ($floatValue != $intValue || floor($floatValue) !== $floatValue) {
                throw new \TypeError(sprintf('Int cannot represent non-integer value: %s', $value));
            }

            return $intValue;
        }

        $this->shared('GraphQLInt', function () {
            return GraphQLScalarType([
                'name'         => TypeNameEnum::INT,
                'description'  =>
                    'The `Int` scalar type represents non-fractional signed whole numeric ' .
                    'values. Int can represent values between -(2^31) and 2^31 - 1.',
                'serialize'    => function ($value) {
                    return coerceInt($value);
                },
                'parseValue'   => function ($value) {
                    return coerceInt($value);
                },
                'parseLiteral' => function (NodeInterface $astNode) {
                    /** @var IntValueNode $astNode */
                    return $astNode->getKind() === NodeKindEnum::INT ? $astNode->getValue() : null;
                },
            ]);
        });

        /**
         * @param $value
         * @return string
         * @throws \TypeError
         */
        function coerceString($value): string
        {
            if ($value === null) {
                return 'null';
            }

            if ($value === true) {
                return 'true';
            }

            if ($value === false) {
                return 'false';
            }

            if (!is_scalar($value)) {
                throw new \TypeError('String cannot represent a non-scalar value');
            }

            return (string)$value;
        }

        $this->shared('GraphQLID', function () {
            return GraphQLScalarType([
                'name'         => TypeNameEnum::ID,
                'description'  =>
                    'The `ID` scalar type represents a unique identifier, often used to ' .
                    'refetch an object or as key for a cache. The ID type appears in a JSON ' .
                    'response as a String; however, it is not intended to be human-readable. ' .
                    'When expected as an input type, any string (such as `"4"`) or integer ' .
                    '(such as `4`) input value will be accepted as an ID.',
                'serialize'    => function ($value) {
                    return coerceString($value);
                },
                'parseValue'   => function ($value) {
                    return coerceString($value);
                },
                'parseLiteral' => function (NodeInterface $astNode) {
                    /** @var StringValueNode $astNode */
                    return in_array($astNode->getKind(), [NodeKindEnum::STRING, NodeKindEnum::INT], true)
                        ? $astNode->getValue()
                        : null;
                },
            ]);
        });

        $this->shared('GraphQLString', function () {
            return GraphQLScalarType([
                'name'         => TypeNameEnum::STRING,
                'description'  =>
                    'The `String` scalar type represents textual data, represented as UTF-8 ' .
                    'character sequences. The String type is most often used by GraphQL to ' .
                    'represent free-form human-readable text.',
                'serialize'    => function ($value) {
                    return coerceString($value);
                },
                'parseValue'   => function ($value) {
                    return coerceString($value);
                },
                'parseLiteral' => function (NodeInterface $astNode) {
                    /** @var StringValueNode $astNode */
                    return $astNode->getKind() === NodeKindEnum::STRING ? $astNode->getValue() : null;
                },
            ]);
        });
    }

    /**
     * Registers the GraphQL directives with the container.
     */
    protected function registerDirectives()
    {
        $this->shared('GraphQLIncludeDirective', function () {
            return GraphQLDirective([
                'name'        => 'include',
                'description' =>
                    'Directs the executor to include this field or fragment only when ' .
                    'the `if` argument is true.',
                'locations'   => [
                    DirectiveLocationEnum::FIELD,
                    DirectiveLocationEnum::FRAGMENT_SPREAD,
                    DirectiveLocationEnum::INLINE_FRAGMENT,
                ],
                'args'        => [
                    'if ' => [
                        'type'        => GraphQLNonNull(GraphQLBoolean()),
                        'description' => 'Included when true.',
                    ],
                ],
            ]);
        });

        $this->shared('GraphQLSkipDirective', function () {
            return GraphQLDirective([
                'name'        => 'skip',
                'description' =>
                    'Directs the executor to skip this field or fragment when the `if` ' .
                    'argument is true.',
                'locations'   => [
                    DirectiveLocationEnum::FIELD,
                    DirectiveLocationEnum::FRAGMENT_SPREAD,
                    DirectiveLocationEnum::INLINE_FRAGMENT,
                ],
                'args'        => [
                    'if' => [
                        'type'        => GraphQLNonNull(GraphQLBoolean()),
                        'description' => 'Skipped when true.',
                    ],
                ],
            ]);
        });

        $this->shared('GraphQLDeprecatedDirective', function () {
            return GraphQLDirective([
                'name'        => 'deprecated',
                'description' => 'Marks an element of a GraphQL schema as no longer supported.',
                'locations'   => [
                    DirectiveLocationEnum::FIELD_DEFINITION,
                    DirectiveLocationEnum::ENUM_VALUE,
                ],
                'args'        => [
                    'reason' => [
                        'type'         => GraphQLString(),
                        'description'  =>
                            'Explains why this element was deprecated, usually also including a ' .
                            'suggestion for how to access supported similar data. Formatted ' .
                            'in [Markdown](https://daringfireball.net/projects/markdown/).',
                        'defaultValue' => DEFAULT_DEPRECATION_REASON,
                    ],
                ]
            ]);
        });
    }

    /**
     * @var array
     */
    private static $supportedNodeBuilders = [
        // Standard
        ArgumentBuilder::class,
        BooleanBuilder::class,
        DirectiveBuilder::class,
        DocumentBuilder::class,
        EnumBuilder::class,
        FieldBuilder::class,
        FloatBuilder::class,
        FragmentDefinitionBuilder::class,
        FragmentSpreadBuilder::class,
        InlineFragmentBuilder::class,
        IntBuilder::class,
        ListBuilder::class,
        ListTypeBuilder::class,
        NameBuilder::class,
        NamedTypeBuilder::class,
        NonNullTypeBuilder::class,
        NullBuilder::class,
        ObjectBuilder::class,
        ObjectFieldBuilder::class,
        OperationDefinitionBuilder::class,
        SelectionSetBuilder::class,
        StringBuilder::class,
        VariableBuilder::class,
        VariableDefinitionBuilder::class,
        // Schema Definition Language (SDL)
        SchemaDefinitionBuilder::class,
        OperationTypeDefinitionBuilder::class,
        FieldDefinitionBuilder::class,
        ScalarTypeDefinitionBuilder::class,
        ObjectTypeDefinitionBuilder::class,
        InterfaceTypeDefinitionBuilder::class,
        UnionTypeDefinitionBuilder::class,
        EnumTypeDefinitionBuilder::class,
        EnumValueDefinitionBuilder::class,
        InputObjectTypeDefinitionBuilder::class,
        InputValueDefinitionBuilder::class,
        ScalarTypeExtensionBuilder::class,
        ObjectTypeExtensionBuilder::class,
        InterfaceTypeExtensionBuilder::class,
        EnumTypeExtensionBuilder::class,
        UnionTypeExtensionBuilder::class,
        InputObjectTypeExtensionBuilder::class,
        DirectiveDefinitionBuilder::class,
    ];

    /**
     * @var array
     */
    private static $supportedSourceReaders = [
        AmpReader::class,
        AtReader::class,
        BangReader::class,
        BlockStringReader::class,
        BraceReader::class,
        BracketReader::class,
        ColonReader::class,
        CommentReader::class,
        DollarReader::class,
        EqualsReader::class,
        NameReader::class,
        NumberReader::class,
        ParenthesisReader::class,
        PipeReader::class,
        SpreadReader::class,
        StringReader::class,
    ];
}
