<?php

namespace Digia\GraphQL;

use Digia\GraphQL\Language\AST\Builder\ArgumentBuilder;
use Digia\GraphQL\Language\AST\Builder\BooleanBuilder;
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
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\StringValueNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;
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
use Digia\GraphQL\Language\Reader\SpreadReader;
use Digia\GraphQL\Language\Reader\StringReader;
use Digia\GraphQL\Type\Definition\TypeNameEnum;
use League\Container\Container;
use League\Container\Definition\DefinitionInterface;
use const Digia\GraphQL\Type\MAX_INT;
use const Digia\GraphQL\Type\MIN_INT;
use function Digia\GraphQL\Type\GraphQLBoolean;
use function Digia\GraphQL\Type\GraphQLDirective;
use function Digia\GraphQL\Type\GraphQLNonNull;
use function Digia\GraphQL\Type\GraphQLScalarType;
use function Digia\GraphQL\Type\GraphQLString;

class GraphQLRuntime
{

    private static $instance;

    /**
     * @var Container
     */
    protected $container;

    /**
     * GraphQL constructor.
     */
    public function __construct()
    {
        $this->container = new Container();

        $this->register();
    }

    /**
     * @return GraphQLRuntime
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function get(string $id)
    {
        return $this->container->get($id);
    }

    /**
     * @param $id
     * @param $concrete
     */
    public function add($id, $concrete)
    {
        $this->container->add($id, $concrete);
    }

    /**
     * @return ParserInterface
     */
    public function getParser(): ParserInterface
    {
        return $this->container->get(ParserInterface::class);
    }

    /**
     * @return LexerInterface
     */
    public function getLexer(): LexerInterface
    {
        return $this->container->get(LexerInterface::class);
    }

    /**
     * @return PrinterInterface
     */
    public function getPrinter(): PrinterInterface
    {
        return $this->container->get(PrinterInterface::class);
    }

    /**
     *
     */
    protected function register()
    {
        $this->registerParser();
        $this->registerPrinter();
        $this->registerScalarTypes();
        $this->registerDirectives();
    }

    /**
     * Registers the GraphQL parser with the container.
     */
    protected function registerParser()
    {
        $this->singleton(NodeBuilderInterface::class, function () {
            $builders = [
                // Standard
                new ArgumentBuilder(),
                new BooleanBuilder(),
                new DirectiveBuilder(),
                new DocumentBuilder(),
                new EnumBuilder(),
                new FieldBuilder(),
                new FloatBuilder(),
                new FragmentDefinitionBuilder(),
                new FragmentSpreadBuilder(),
                new InlineFragmentBuilder(),
                new IntBuilder(),
                new ListBuilder(),
                new ListTypeBuilder(),
                new NameBuilder(),
                new NamedTypeBuilder(),
                new NonNullTypeBuilder(),
                new NullBuilder(),
                new ObjectBuilder(),
                new ObjectFieldBuilder(),
                new OperationDefinitionBuilder(),
                new SelectionSetBuilder(),
                new StringBuilder(),
                new VariableBuilder(),
                new VariableDefinitionBuilder(),
                // Schema Definition Language (SDL)
                new SchemaDefinitionBuilder(),
                new OperationTypeDefinitionBuilder(),
                new FieldDefinitionBuilder(),
                new ScalarTypeDefinitionBuilder(),
                new ObjectTypeDefinitionBuilder(),
                new InterfaceTypeDefinitionBuilder(),
                new UnionTypeDefinitionBuilder(),
                new EnumTypeDefinitionBuilder(),
                new EnumValueDefinitionBuilder(),
                new InputObjectTypeDefinitionBuilder(),
                new InputValueDefinitionBuilder(),
                new ScalarTypeExtensionBuilder(),
                new ObjectTypeExtensionBuilder(),
                new InterfaceTypeExtensionBuilder(),
                new EnumTypeExtensionBuilder(),
                new UnionTypeExtensionBuilder(),
                new InputObjectTypeExtensionBuilder(),
                new DirectiveDefinitionBuilder(),
            ];

            return new NodeBuilder($builders);
        });

        $this
            ->singleton(ParserInterface::class, Parser::class)
            ->addArgument(NodeBuilderInterface::class);

        $this->container->add(LexerInterface::class, function () {
            $readers = [
                new AmpReader(),
                new AtReader(),
                new BangReader(),
                new BlockStringReader(),
                new BraceReader(),
                new BracketReader(),
                new ColonReader(),
                new CommentReader(),
                new DollarReader(),
                new EqualsReader(),
                new NameReader(),
                new NumberReader(),
                new ParenthesisReader(),
                new PipeReader(),
                new SpreadReader(),
                new StringReader(),
            ];

            return new Lexer($readers);
        });
    }

    /**
     * Registers the GraphQL printer with the container.
     */
    protected function registerPrinter()
    {
        $this->singleton(PrinterInterface::class, Printer::class);
    }

    /**
     * Registers the GraphQL scalar types with the container.
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

        $this->singleton('GraphQLBoolean', function () {
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

        $this->singleton('GraphQLFloat', function () {
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

        $this->singleton('GraphQLInt', function () {
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

        $this->singleton('GraphQLID', function () {
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

        $this->singleton('GraphQLString', function () {
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
        $this->singleton('GraphQLIncludeDirective', function () {
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

        $this->singleton('GraphQLSkipDirective', function () {
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

        $this->singleton('GraphQLDeprecatedDirective', function () {
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
     * @param string $id
     * @param mixed  $concrete
     * @return DefinitionInterface
     */
    protected function bind(string $id, $concrete): DefinitionInterface
    {
        return $this->container->add($id, $concrete);
    }

    /**
     * @param string $id
     * @param mixed  $concrete
     * @return DefinitionInterface
     */
    protected function singleton(string $id, $concrete): DefinitionInterface
    {
        return $this->container->add($id, $concrete, true);
    }
}
