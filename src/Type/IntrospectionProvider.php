<?php

namespace Digia\GraphQL\Type;

use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Execution\ResolveInfo;
use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Language\DirectiveLocationEnum;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Type\Definition\AbstractTypeInterface;
use Digia\GraphQL\Type\Definition\ArgumentsAwareInterface;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\UnionType;
use League\Container\ServiceProvider\AbstractServiceProvider;

class IntrospectionProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        // Introspection types
        GraphQL::SCHEMA_INTROSPECTION,
        GraphQL::DIRECTIVE_INTROSPECTION,
        GraphQL::DIRECTIVE_LOCATION_INTROSPECTION,
        GraphQL::TYPE_INTROSPECTION,
        GraphQL::FIELD_INTROSPECTION,
        GraphQL::INPUT_VALUE_INTROSPECTION,
        GraphQL::ENUM_VALUE_INTROSPECTION,
        GraphQL::TYPE_KIND_INTROSPECTION,
        // Meta fields
        GraphQL::SCHEMA_META_FIELD_DEFINITION,
        GraphQL::TYPE_META_FIELD_DEFINITION,
        GraphQL::TYPE_NAME_META_FIELD_DEFINITION,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->registerIntrospectionTypes();
        $this->registerMetaFields();
    }

    /**
     * Registers the introspection types with the container.
     */
    protected function registerIntrospectionTypes()
    {
        $this->container
            ->share(GraphQL::SCHEMA_INTROSPECTION, function () {
                return newObjectType([
                    'name'            => GraphQL::SCHEMA_INTROSPECTION,
                    'isIntrospection' => true,
                    'description'     =>
                        'A GraphQL Schema defines the capabilities of a GraphQL server. It ' .
                        'exposes all available types and directives on the server, as well as ' .
                        'the entry points for query, mutation, and subscription operations.',
                    'fields'          => function () {
                        return [
                            'types'            => [
                                'description' => 'A list of all types supported by this server.',
                                'type'        => newNonNull(newList(newNonNull(__Type()))),
                                'resolve'     => function (Schema $schema): array {
                                    return \array_values($schema->getTypeMap());
                                },
                            ],
                            'queryType'        => [
                                'description' => 'The type that query operations will be rooted at.',
                                'type'        => newNonNull(__Type()),
                                'resolve'     => function (Schema $schema): ?TypeInterface {
                                    return $schema->getQueryType();
                                },
                            ],
                            'mutationType'     => [
                                'description' =>
                                    'If this server supports mutation, the type that ' .
                                    'mutation operations will be rooted at.',
                                'type'        => __Type(),
                                'resolve'     => function (Schema $schema): ?TypeInterface {
                                    return $schema->getMutationType();
                                },
                            ],
                            'subscriptionType' => [
                                'description' =>
                                    'If this server support subscription, the type that ' .
                                    'subscription operations will be rooted at.',
                                'type'        => __Type(),
                                'resolve'     => function (Schema $schema): ?TypeInterface {
                                    return $schema->getSubscriptionType();
                                },
                            ],
                            'directives'       => [
                                'description' => 'A list of all directives supported by this server.',
                                'type'        => newNonNull(newList(newNonNull(__Directive()))),
                                'resolve'     => function (Schema $schema): array {
                                    return $schema->getDirectives();
                                },
                            ],
                        ];
                    }
                ]);
            });

        $this->container
            ->share(GraphQL::DIRECTIVE_INTROSPECTION, function () {
                return newObjectType([
                    'name'            => GraphQL::DIRECTIVE_INTROSPECTION,
                    'isIntrospection' => true,
                    'description'     =>
                        'A Directive provides a way to describe alternate runtime execution and ' .
                        'type validation behavior in a GraphQL document.' .
                        "\n\nIn some cases, you need to provide options to alter GraphQL's " .
                        'execution behavior in ways field arguments will not suffice, such as ' .
                        'conditionally including or skipping a field. Directives provide this by ' .
                        'describing additional information to the executor.',
                    'fields'          => function () {
                        return [
                            'name'        => ['type' => newNonNull(stringType())],
                            'description' => ['type' => stringType()],
                            'locations'   => [
                                'type' => newNonNull(newList(newNonNull(__DirectiveLocation()))),
                            ],
                            'args'        => [
                                'type'    => newNonNull(newList(newNonNull(__InputValue()))),
                                'resolve' => function (Directive $directive): array {
                                    return $directive->getArguments() ?: [];
                                },
                            ],
                        ];
                    }
                ]);
            });

        $this->container
            ->share(GraphQL::DIRECTIVE_LOCATION_INTROSPECTION, function () {
                return newEnumType([
                    'name'            => GraphQL::DIRECTIVE_LOCATION_INTROSPECTION,
                    'isIntrospection' => true,
                    'description'     =>
                        'A Directive can be adjacent to many parts of the GraphQL language, a ' .
                        '__DirectiveLocation describes one such possible adjacencies.',
                    'values'          => [
                        DirectiveLocationEnum::QUERY                  => [
                            'description' => 'Location adjacent to a query operation.',
                        ],
                        DirectiveLocationEnum::MUTATION               => [
                            'description' => 'Location adjacent to a mutation operation.',
                        ],
                        DirectiveLocationEnum::SUBSCRIPTION           => [
                            'description' => 'Location adjacent to a subscription operation.',
                        ],
                        DirectiveLocationEnum::FIELD                  => [
                            'description' => 'Location adjacent to a field.',
                        ],
                        DirectiveLocationEnum::FRAGMENT_DEFINITION    => [
                            'description' => 'Location adjacent to a fragment definition.',
                        ],
                        DirectiveLocationEnum::FRAGMENT_SPREAD        => [
                            'description' => 'Location adjacent to a fragment spread.',
                        ],
                        DirectiveLocationEnum::INLINE_FRAGMENT        => [
                            'description' => 'Location adjacent to an inline fragment.',
                        ],
                        DirectiveLocationEnum::VARIABLE_DEFINITION => [
                            'description' => 'Location adjacent to a variable definition.',
                        ],
                        DirectiveLocationEnum::SCHEMA                 => [
                            'description' => 'Location adjacent to a schema definition.',
                        ],
                        DirectiveLocationEnum::SCALAR                 => [
                            'description' => 'Location adjacent to a scalar definition.',
                        ],
                        DirectiveLocationEnum::OBJECT                 => [
                            'description' => 'Location adjacent to an object type definition.',
                        ],
                        DirectiveLocationEnum::FIELD_DEFINITION       => [
                            'description' => 'Location adjacent to a field definition.',
                        ],
                        DirectiveLocationEnum::ARGUMENT_DEFINITION    => [
                            'description' => 'Location adjacent to an argument definition.',
                        ],
                        DirectiveLocationEnum::INTERFACE              => [
                            'description' => 'Location adjacent to an interface definition.',
                        ],
                        DirectiveLocationEnum::UNION                  => [
                            'description' => 'Location adjacent to a union definition.',
                        ],
                        DirectiveLocationEnum::ENUM                   => [
                            'description' => 'Location adjacent to an enum definition.',
                        ],
                        DirectiveLocationEnum::ENUM_VALUE             => [
                            'description' => 'Location adjacent to an enum value definition.',
                        ],
                        DirectiveLocationEnum::INPUT_OBJECT           => [
                            'description' => 'Location adjacent to an input object type definition.',
                        ],
                        DirectiveLocationEnum::INPUT_FIELD_DEFINITION => [
                            'description' => 'Location adjacent to an input object field definition.',
                        ],
                    ],
                ]);
            });

        $this->container
            ->share(GraphQL::TYPE_INTROSPECTION, function () {
                return newObjectType([
                    'name'            => GraphQL::TYPE_INTROSPECTION,
                    'isIntrospection' => true,
                    'description'     =>
                        'The fundamental unit of any GraphQL Schema is the type. There are many kinds of ' .
                        "types in GraphQL as represented by the `__TypeKind` enum.\n\n" .
                        'Depending on the kind of a type, certain fields describe information about that ' .
                        'type. Scalar types provide no information beyond a name and description, while ' .
                        'Enum types provide their values. Object and Interface types provide the fields ' .
                        'they describe. Abstract types, Union and Interface, provide the Object types ' .
                        'possible at runtime. List and NonNull types compose other types.',
                    'fields'          => function () {
                        return [
                            'kind'          => [
                                'type'    => newNonNull(__TypeKind()),
                                'resolve' => function (TypeInterface $type) {
                                    if ($type instanceof ScalarType) {
                                        return TypeKindEnum::SCALAR;
                                    }
                                    if ($type instanceof ObjectType) {
                                        return TypeKindEnum::OBJECT;
                                    }
                                    if ($type instanceof InterfaceType) {
                                        return TypeKindEnum::INTERFACE;
                                    }
                                    if ($type instanceof UnionType) {
                                        return TypeKindEnum::UNION;
                                    }
                                    if ($type instanceof EnumType) {
                                        return TypeKindEnum::ENUM;
                                    }
                                    if ($type instanceof InputObjectType) {
                                        return TypeKindEnum::INPUT_OBJECT;
                                    }
                                    if ($type instanceof ListType) {
                                        return TypeKindEnum::LIST;
                                    }
                                    if ($type instanceof NonNullType) {
                                        return TypeKindEnum::NON_NULL;
                                    }

                                    throw new InvalidTypeException(\sprintf('Unknown kind of type: %s', (string)$type));
                                },
                            ],
                            'name'          => ['type' => stringType()],
                            'description'   => ['type' => stringType()],
                            'fields'        => [
                                'type'    => newList(newNonNull(__Field())),
                                'args'    => [
                                    'includeDeprecated' => ['type' => booleanType(), 'defaultValue' => false],
                                ],
                                'resolve' => function (TypeInterface $type, array $args):
                                ?array {
                                    ['includeDeprecated' => $includeDeprecated] = $args;

                                    if ($type instanceof ObjectType || $type instanceof InterfaceType) {
                                        $fields = \array_values($type->getFields());

                                        if (!$includeDeprecated) {
                                            $fields = \array_filter($fields, function (Field $field) {
                                                return !$field->isDeprecated();
                                            });
                                        }

                                        return $fields;
                                    }

                                    return null;
                                },
                            ],
                            'interfaces'    => [
                                'type'    => newList(newNonNull(__Type())),
                                'resolve' => function (TypeInterface $type): ?array {
                                    return $type instanceof ObjectType ? $type->getInterfaces() : null;
                                },
                            ],
                            'possibleTypes' => [
                                'type'    => newList(newNonNull(__Type())),
                                'resolve' => function (
                                    TypeInterface $type,
                                    array $args,
                                    array $context,
                                    ResolveInfo $info
                                ):
                                ?array {
                                    /** @var Schema $schema */
                                    $schema = $info->getSchema();
                                    /** @noinspection PhpParamsInspection */
                                    return $type instanceof AbstractTypeInterface ? $schema->getPossibleTypes($type) : null;
                                },
                            ],
                            'enumValues'    => [
                                'type'    => newList(newNonNull(__EnumValue())),
                                'args'    => [
                                    'includeDeprecated' => ['type' => booleanType(), 'defaultValue' => false],
                                ],
                                'resolve' => function (TypeInterface $type, array $args): ?array {
                                    ['includeDeprecated' => $includeDeprecated] = $args;

                                    if ($type instanceof EnumType) {
                                        $values = \array_values($type->getValues());

                                        if (!$includeDeprecated) {
                                            $values = \array_filter($values, function (Field $field) {
                                                return !$field->isDeprecated();
                                            });
                                        }

                                        return $values;
                                    }

                                    return null;
                                },
                            ],
                            'inputFields'   => [
                                'type'    => newList(newNonNull(__InputValue())),
                                'resolve' => function (TypeInterface $type): ?array {
                                    return $type instanceof InputObjectType ? $type->getFields() : null;
                                },
                            ],
                            'ofType'        => ['type' => __Type()],
                        ];
                    }
                ]);
            });

        $this->container
            ->share(GraphQL::FIELD_INTROSPECTION, function () {
                return newObjectType([
                    'name'            => GraphQL::FIELD_INTROSPECTION,
                    'isIntrospection' => true,
                    'description'     =>
                        'Object and Interface types are described by a list of Fields, each of ' .
                        'which has a name, potentially a list of arguments, and a return type.',
                    'fields'          => function () {
                        return [
                            'name'              => ['type' => newNonNull(stringType())],
                            'description'       => ['type' => stringType()],
                            'args'              => [
                                'type'    => newNonNull(newList(newNonNull(__InputValue()))),
                                'resolve' => function (ArgumentsAwareInterface $directive): array {
                                    return $directive->getArguments() ?? [];
                                },
                            ],
                            'type'              => ['type' => newNonNull(__Type())],
                            'isDeprecated'      => ['type' => newNonNull(booleanType())],
                            'deprecationReason' => ['type' => stringType()],
                        ];
                    }
                ]);
            });

        $this->container
            ->share(GraphQL::INPUT_VALUE_INTROSPECTION, function () {
                return newObjectType([
                    'name'            => GraphQL::INPUT_VALUE_INTROSPECTION,
                    'isIntrospection' => true,
                    'description'     =>
                        'Arguments provided to Fields or Directives and the input fields of an ' .
                        'InputObject are represented as Input Values which describe their type ' .
                        'and optionally a default value.',
                    'fields'          => function () {
                        return [
                            'name'         => ['type' => newNonNull(stringType())],
                            'description'  => ['type' => stringType()],
                            'type'         => ['type' => newNonNull(__Type())],
                            'defaultValue' => [
                                'type'        => stringType(),
                                'description' =>
                                    'A GraphQL-formatted string representing the default value for this ' .
                                    'input value.',
                                'resolve'     => function (/*$inputValue*/) {
                                    // TODO: Implement this when we have support for printing AST.
                                    return null;
                                }
                            ],
                        ];
                    }
                ]);
            });

        $this->container
            ->share(GraphQL::ENUM_VALUE_INTROSPECTION, function () {
                return newObjectType([
                    'name'            => GraphQL::ENUM_VALUE_INTROSPECTION,
                    'isIntrospection' => true,
                    'description'     =>
                        'One possible value for a given Enum. Enum values are unique values, not ' .
                        'a placeholder for a string or numeric value. However an Enum value is ' .
                        'returned in a JSON response as a string.',
                    'fields'          => function () {
                        return [
                            'name'              => ['type' => newNonNull(stringType())],
                            'description'       => ['type' => stringType()],
                            'isDeprecated'      => ['type' => newNonNull(booleanType())],
                            'deprecationReason' => ['type' => stringType()],
                        ];
                    }
                ]);
            });

        $this->container
            ->share(GraphQL::TYPE_KIND_INTROSPECTION, function () {
                return newEnumType([
                    'name'            => GraphQL::TYPE_KIND_INTROSPECTION,
                    'isIntrospection' => true,
                    'description'     => 'An enum describing what kind of type a given `__Type` is.',
                    'values'          => [
                        TypeKindEnum::SCALAR       => [
                            'description' => 'Indicates this type is a scalar.',
                        ],
                        TypeKindEnum::OBJECT       => [
                            'description' => 'Indicates this type is an object. `fields` and `interfaces` are valid fields.',
                        ],
                        TypeKindEnum::INTERFACE    => [
                            'description' => 'Indicates this type is an interface. `fields` and `possibleTypes` are valid fields.',
                        ],
                        TypeKindEnum::UNION        => [
                            'description' => 'Indicates this type is a union. `possibleTypes` is a valid field.',
                        ],
                        TypeKindEnum::ENUM         => [
                            'description' => 'Indicates this type is an enum. `enumValues` is a valid field.',
                        ],
                        TypeKindEnum::INPUT_OBJECT => [
                            'description' => 'Indicates this type is an input object. `inputFields` is a valid field.',
                        ],
                        TypeKindEnum::LIST         => [
                            'description' => 'Indicates this type is a list. `ofType` is a valid field.',
                        ],
                        TypeKindEnum::NON_NULL     => [
                            'description' => 'Indicates this type is a non-null. `ofType` is a valid field.',
                        ],
                    ],
                ]);
            });
    }

    /**
     * Registers the introspection meta fields with the container.
     */
    protected function registerMetaFields()
    {
        $this->container
            ->share(GraphQL::SCHEMA_META_FIELD_DEFINITION, function ($__Schema) {
                return newField([
                    'name'        => '__schema',
                    'description' => 'Access the current type schema of this server.',
                    'type'        => newNonNull($__Schema),
                    'resolve'     => function ($source, $args, $context, ResolveInfo $info): Schema {
                        return $info->getSchema();
                    },
                ]);
            })
            ->addArgument(GraphQL::SCHEMA_INTROSPECTION);

        $this->container
            ->share(GraphQL::TYPE_META_FIELD_DEFINITION, function ($__Type) {
                return newField([
                    'name'        => '__type',
                    'description' => 'Request the type information of a single type.',
                    'type'        => $__Type,
                    'args'        => ['name' => ['type' => newNonNull(stringType())]],
                    'resolve'     => function ($source, $args, $context, ResolveInfo $info): ?TypeInterface {
                        ['name' => $name] = $args;
                        return $info->getSchema()->getType($name);
                    },
                ]);
            })
            ->addArgument(GraphQL::TYPE_INTROSPECTION);

        $this->container
            ->share(GraphQL::TYPE_NAME_META_FIELD_DEFINITION, function () {
                return newField([
                    'name'        => '__typename',
                    'description' => 'The name of the current Object type at runtime.',
                    'type'        => newNonNull(stringType()),
                    'resolve'     => function ($source, $args, $context, ResolveInfo $info): string {
                        return $info->getParentType()->getName();
                    },
                ]);
            });
    }
}
