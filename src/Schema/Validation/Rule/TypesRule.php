<?php

namespace Digia\GraphQL\Schema\Validation\Rule;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\EnumTypeDefinitionNode;
use Digia\GraphQL\Language\Node\FieldDefinitionNode;
use Digia\GraphQL\Language\Node\InputValueDefinitionNode;
use Digia\GraphQL\Language\Node\NameAwareInterface;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\TypeNodeInterface;
use Digia\GraphQL\Language\Node\UnionTypeDefinitionNode;
use Digia\GraphQL\Schema\Validation\SchemaValidationException;
use Digia\GraphQL\Type\Definition\Argument;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\FieldsAwareInterface;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\UnionType;
use Digia\GraphQL\Util\NameHelper;
use Digia\GraphQL\Util\TypeHelper;
use function Digia\GraphQL\Type\isInputType;
use function Digia\GraphQL\Type\isIntrospectionType;
use function Digia\GraphQL\Type\isOutputType;
use function Digia\GraphQL\Util\find;
use function Digia\GraphQL\Util\toString;

class TypesRule extends AbstractRule
{
    /**
     * @inheritdoc
     *
     * @throws InvariantException
     */
    public function evaluate(): void
    {
        $typeMap = $this->context->getSchema()->getTypeMap();

        foreach ($typeMap as $type) {
            if (!($type instanceof NamedTypeInterface)) {
                $this->context->reportError(
                    new SchemaValidationException(
                        \sprintf('Expected GraphQL named type but got: %s.', toString($type)),
                        $type instanceof ASTNodeAwareInterface ? [$type->getAstNode()] : null
                    )
                );

                continue;
            }

            // Ensure it is named correctly (excluding introspection types).
            /** @noinspection PhpParamsInspection */
            if (!isIntrospectionType($type)) {
                $this->validateName($type);
            }

            if ($type instanceof ObjectType) {
                // Ensure fields are valid.
                $this->validateFields($type);
                // Ensure objects implement the interfaces they claim to.
                $this->validateObjectInterfaces($type);
                continue;
            }

            if ($type instanceof InterfaceType) {
                // Ensure fields are valid.
                $this->validateFields($type);
                continue;
            }

            if ($type instanceof UnionType) {
                // Ensure Unions include valid member types.
                $this->validateUnionMembers($type);
                continue;
            }

            if ($type instanceof EnumType) {
                // Ensure Enums have valid values.
                $this->validateEnumValues($type);
                continue;
            }

            if ($type instanceof InputObjectType) {
                // Ensure Input Object fields are valid.
                $this->validateInputFields($type);
                continue;
            }
        }
    }

    /**
     * @param FieldsAwareInterface $type
     * @throws InvariantException
     */
    protected function validateFields(FieldsAwareInterface $type): void
    {
        $fields = $type->getFields();

        // Objects and Interfaces both must define one or more fields.
        if (empty($fields)) {
            $this->context->reportError(
                new SchemaValidationException(
                    \sprintf('Type %s must define one or more fields.', $type->getName()),
                    $this->getAllObjectOrInterfaceNodes($type)
                )
            );
        }

        foreach ($fields as $fieldName => $field) {
            // Ensure they are named correctly.
            $this->validateName($field);

            // Ensure they were defined at most once.
            $fieldNodes = $this->getAllFieldNodes($type, $fieldName);

            if (\count($fieldNodes) > 1) {
                $this->context->reportError(
                    new SchemaValidationException(
                        \sprintf('Field %s.%s can only be defined once.', $type->getName(), $fieldName),
                        $fieldNodes
                    )
                );

                return; // continue loop
            }

            $fieldType = $field->getType();

            // Ensure the type is an output type
            if (!isOutputType($fieldType)) {
                $fieldTypeNode = $this->getFieldTypeNode($type, $fieldName);
                $this->context->reportError(
                    new SchemaValidationException(
                        \sprintf(
                            'The type of %s.%s must be Output Type but got: %s.',
                            $type->getName(),
                            $fieldName,
                            toString($fieldType)
                        ),
                        [$fieldTypeNode]
                    )
                );
            }

            // Ensure the arguments are valid
            $argumentNames = [];

            foreach ($field->getArguments() as $argument) {
                $argumentName = $argument->getName();

                // Ensure they are named correctly.
                $this->validateName($argument);

                // Ensure they are unique per field.
                if (isset($argumentNames[$argumentName])) {
                    $this->context->reportError(
                        new SchemaValidationException(
                            \sprintf(
                                'Field argument %s.%s(%s:) can only be defined once.',
                                $type->getName(),
                                $field->getName(),
                                $argumentName
                            ),
                            $this->getAllFieldArgumentNodes($type, $fieldName, $argumentName)
                        )
                    );
                }

                $argumentNames[$argumentName] = true;

                // Ensure the type is an input type
                if (!isInputType($argument->getType())) {
                    $this->context->reportError(
                        new SchemaValidationException(
                            \sprintf(
                                'The type of %s.%s(%s:) must be Input Type but got: %s.',
                                $type->getName(),
                                $fieldName,
                                $argumentName,
                                toString($argument->getType())
                            ),
                            $this->getAllFieldArgumentNodes($type, $fieldName, $argumentName)
                        )
                    );
                }
            }
        }
    }

    /**
     * @param ObjectType $objectType
     * @throws InvariantException
     */
    protected function validateObjectInterfaces(ObjectType $objectType): void
    {
        $implementedTypeNames = [];

        foreach ($objectType->getInterfaces() as $interface) {
            if (!($interface instanceof InterfaceType)) {
                $this->context->reportError(
                    new SchemaValidationException(
                        \sprintf(
                            'Type %s must only implement Interface types, it cannot implement %s.',
                            toString($objectType),
                            toString($interface)
                        ),
                        null !== $interface
                            ? [$this->getImplementsInterfaceNode($objectType, $interface->getName())]
                            : null
                    )
                );

                continue;
            }

            $interfaceName = $interface->getName();

            if (isset($implementedTypeNames[$interfaceName])) {
                $this->context->reportError(
                    new SchemaValidationException(
                        \sprintf('Type %s can only implement %s once.', $objectType->getName(), $interfaceName),
                        $this->getAllImplementsInterfaceNodes($objectType, $interfaceName)
                    )
                );

                continue;
            }

            $implementedTypeNames[$interfaceName] = true;

            $this->validateObjectImplementsInterface($objectType, $interface);
        }
    }

    /**
     * @param ObjectType    $objectType
     * @param InterfaceType $interfaceType
     * @throws InvariantException
     */
    protected function validateObjectImplementsInterface(ObjectType $objectType, InterfaceType $interfaceType): void
    {
        $objectFields    = $objectType->getFields();
        $interfaceFields = $interfaceType->getFields();

        // Assert each interface field is implemented.
        foreach (\array_keys($interfaceFields) as $fieldName) {
            $interfaceField = $interfaceFields[$fieldName];
            $objectField    = $objectFields[$fieldName] ?? null;

            // Assert interface field exists on object.
            if (null === $objectField) {
                $this->context->reportError(
                    new SchemaValidationException(
                        \sprintf(
                            'Interface field %s.%s expected but %s does not provide it.',
                            $interfaceType->getName(),
                            $fieldName,
                            $objectType->getName()
                        ),
                        [$this->getFieldNode($interfaceType, $fieldName), $objectType->getAstNode()]
                    )
                );

                continue;
            }

            // Assert interface field type is satisfied by object field type, by being
            // a valid subtype. (covariant)
            if (!TypeHelper::isTypeSubtypeOf(
                $this->context->getSchema(), $objectField->getType(), $interfaceField->getType())) {
                $this->context->reportError(
                    new SchemaValidationException(
                        \sprintf(
                            'Interface field %s.%s expects type %s but %s.%s is type %s.',
                            $interfaceType->getName(),
                            $fieldName,
                            toString($interfaceField->getType()),
                            $objectType->getName(),
                            $fieldName,
                            toString($objectField->getType())
                        ),
                        [
                            $this->getFieldTypeNode($interfaceType, $fieldName),
                            $this->getFieldTypeNode($objectType, $fieldName),
                        ]
                    )
                );
            }

            // Assert each interface field arg is implemented.
            foreach ($interfaceField->getArguments() as $interfaceArgument) {
                $argumentName   = $interfaceArgument->getName();
                $objectArgument = find($objectField->getArguments(), function (Argument $argument) use ($argumentName) {
                    return $argument->getName() === $argumentName;
                });

                // Assert interface field arg exists on object field.
                if (null === $objectArgument) {
                    $this->context->reportError(
                        new SchemaValidationException(
                            \sprintf(
                                'Interface field argument %s.%s(%s:) expected but %s.%s does not provide it.',
                                $interfaceType->getName(),
                                $fieldName,
                                $argumentName,
                                $objectType->getName(),
                                $fieldName
                            ),
                            [
                                $this->getFieldArgumentNode($interfaceType, $fieldName, $argumentName),
                                $this->getFieldNode($objectType, $fieldName),
                            ]
                        )
                    );

                    continue;
                }

                // Assert interface field arg type matches object field arg type.
                // (invariant)
                // TODO: change to contravariant?
                if (!TypeHelper::isEqualType($interfaceArgument->getType(), $objectArgument->getType())) {
                    $this->context->reportError(
                        new SchemaValidationException(
                            \sprintf(
                                'Interface field argument %s.%s(%s:) expects type %s but %s.%s(%s:) is type %s.',
                                $interfaceType->getName(),
                                $fieldName,
                                $argumentName,
                                toString($interfaceArgument->getType()),
                                $objectType->getName(),
                                $fieldName,
                                $argumentName,
                                toString($objectArgument->getType())
                            ),
                            [
                                $this->getFieldArgumentTypeNode($interfaceType, $fieldName, $argumentName),
                                $this->getFieldArgumentTypeNode($objectType, $fieldName, $argumentName),
                            ]
                        )
                    );

                    continue;
                }

                // TODO: validate default values?

                foreach ($objectField->getArguments() as $objectArgument) {
                    $argumentName      = $objectArgument->getName();
                    $interfaceArgument = find(
                        $interfaceField->getArguments(),
                        function (Argument $argument) use ($argumentName) {
                            return $argument->getName() === $argumentName;
                        }
                    );

                    if (null === $interfaceArgument && $objectArgument->getType() instanceof NonNullType) {
                        $this->context->reportError(
                            new SchemaValidationException(
                                \sprintf(
                                    'Object field argument %s.%s(%s:) is of required type %s ' .
                                    'but is not also provided by the Interface field %s.%s.',
                                    $objectType->getName(),
                                    $fieldName,
                                    $argumentName,
                                    toString($objectArgument->getType()),
                                    $interfaceType->getName(),
                                    $fieldName
                                ),
                                [
                                    $this->getFieldArgumentNode($objectType, $fieldName, $argumentName),
                                    $this->getFieldNode($interfaceType, $fieldName),
                                ]
                            )
                        );

                        continue;
                    }
                }
            }
        }
    }

    /**
     * @param UnionType $unionType
     * @throws InvariantException
     */
    protected function validateUnionMembers(UnionType $unionType): void
    {
        $memberTypes = $unionType->getTypes();

        if (empty($memberTypes)) {
            $this->context->reportError(
                new SchemaValidationException(
                    sprintf('Union type %s must define one or more member types.', $unionType->getName()),
                    [$unionType->getAstNode()]
                )
            );
        }

        $includedTypeNames = [];

        foreach ($memberTypes as $memberType) {
            $memberTypeName = (string)$memberType;
            if (isset($includedTypeNames[$memberTypeName])) {
                $this->context->reportError(
                    new SchemaValidationException(
                        \sprintf(
                            'Union type %s can only include type %s once.',
                            $unionType->getName(),
                            $memberTypeName
                        ),
                        $this->getUnionMemberTypeNodes($unionType, $memberTypeName)
                    )
                );

                continue;
            }

            $includedTypeNames[$memberTypeName] = true;

            if (!($memberType instanceof ObjectType)) {
                $this->context->reportError(
                    new SchemaValidationException(
                        \sprintf(
                            'Union type %s can only include Object types, it cannot include %s.',
                            $unionType->getName(),
                            toString($memberType)
                        ),
                        $this->getUnionMemberTypeNodes($unionType, $memberTypeName)
                    )
                );
            }
        }
    }

    /**
     * @param EnumType $enumType
     * @throws InvariantException
     */
    protected function validateEnumValues(EnumType $enumType): void
    {
        $enumValues = $enumType->getValues();

        if (empty($enumValues)) {
            $this->context->reportError(
                new SchemaValidationException(
                    \sprintf('Enum type %s must define one or more values.', $enumType->getName()),
                    [$enumType->getAstNode()]
                )
            );
        }

        foreach ($enumValues as $enumValue) {
            $valueName = $enumValue->getName();

            // Ensure no duplicates.
            $allNodes = $this->getEnumValueNodes($enumType, $valueName);

            if (null !== $allNodes && \count($allNodes) > 1) {
                $this->context->reportError(
                    new SchemaValidationException(
                        sprintf('Enum type %s can include value %s only once.', $enumType->getName(), $valueName),
                        $allNodes
                    )
                );

                continue;
            }

            // Ensure valid name.
            $this->validateName($enumValue);

            if ($valueName === 'true' || $valueName === 'false' || $valueName === 'null') {
                $this->context->reportError(
                    new SchemaValidationException(
                        sprintf('Enum type %s cannot include value: %s.', $enumType->getName(), $valueName),
                        [$enumValue->getAstNode()]
                    )
                );

                continue;
            }
        }
    }

    /**
     * @param InputObjectType $inputObjectType
     * @throws InvariantException
     */
    protected function validateInputFields(InputObjectType $inputObjectType): void
    {
        $fields = $inputObjectType->getFields();

        if (empty($fields)) {
            $this->context->reportError(
                new SchemaValidationException(
                    \sprintf('Input Object type %s must define one or more fields.', $inputObjectType->getName()),
                    [$inputObjectType->getAstNode()]
                )
            );
        }

        // Ensure the arguments are valid
        foreach ($fields as $fieldName => $field) {
            // Ensure they are named correctly.
            $this->validateName($field);

            // TODO: Ensure they are unique per field.

            // Ensure the type is an input type
            if (!isInputType($field->getType())) {
                $this->context->reportError(
                    new SchemaValidationException(
                        \sprintf(
                            'The type of %s.%s must be Input Type but got: %s.',
                            $inputObjectType->getName(),
                            $fieldName,
                            toString($field->getType())
                        ),
                        [$field->getAstNode()]
                    )
                );
            }
        }
    }

    /**
     * @param FieldsAwareInterface $type
     * @return array
     */
    protected function getAllObjectOrInterfaceNodes(FieldsAwareInterface $type): array
    {
        $node              = $type->getAstNode();
        $extensionASTNodes = $type->getExtensionAstNodes();

        if (null !== $node) {
            return !empty($extensionASTNodes)
                ? \array_merge([$node], $extensionASTNodes)
                : [$node];
        }

        return $extensionASTNodes;
    }

    /**
     * @param FieldsAwareInterface $type
     * @param string               $fieldName
     * @return TypeNodeInterface|null
     */
    protected function getFieldTypeNode(FieldsAwareInterface $type, string $fieldName): ?TypeNodeInterface
    {
        $fieldNode = $this->getFieldNode($type, $fieldName);
        return null !== $fieldNode ? $fieldNode->getType() : null;
    }

    /**
     * @param FieldsAwareInterface $type
     * @param string               $fieldName
     * @return FieldDefinitionNode|null
     */
    protected function getFieldNode(FieldsAwareInterface $type, string $fieldName): ?FieldDefinitionNode
    {
        return $this->getAllFieldNodes($type, $fieldName)[0] ?? null;
    }

    /**
     * @param FieldsAwareInterface $type
     * @param string               $fieldName
     * @return FieldDefinitionNode[]
     */
    protected function getAllFieldNodes(FieldsAwareInterface $type, string $fieldName): array
    {
        $nodes = [];

        foreach ($this->getAllObjectOrInterfaceNodes($type) as $objectOrInterface) {
            foreach ($objectOrInterface->getFields() as $node) {
                if ($node->getNameValue() === $fieldName) {
                    $nodes[] = $node;
                }
            }
        }

        return $nodes;
    }

    /**
     * @param FieldsAwareInterface $type
     * @param string               $fieldName
     * @param string               $argumentName
     * @return InputValueDefinitionNode|null
     */
    protected function getFieldArgumentNode(
        FieldsAwareInterface $type,
        string $fieldName,
        string $argumentName
    ): ?InputValueDefinitionNode {
        return $this->getAllFieldArgumentNodes($type, $fieldName, $argumentName)[0] ?? null;
    }

    /**
     * @param FieldsAwareInterface $type
     * @param string               $fieldName
     * @param string               $argumentName
     * @return InputValueDefinitionNode[]
     */
    protected function getAllFieldArgumentNodes(
        FieldsAwareInterface $type,
        string $fieldName,
        string $argumentName
    ): array {
        $nodes = [];

        $fieldNode = $this->getFieldNode($type, $fieldName);

        if (null !== $fieldNode) {
            foreach ($fieldNode->getArguments() as $node) {
                if ($node->getNameValue() === $argumentName) {
                    $nodes[] = $node;
                }
            }
        }

        return $nodes;
    }

    /**
     * @param ObjectType $type
     * @param string     $interfaceName
     * @return NamedTypeNode|null
     */
    protected function getImplementsInterfaceNode(ObjectType $type, string $interfaceName): ?NamedTypeNode
    {
        return $this->getAllImplementsInterfaceNodes($type, $interfaceName)[0] ?? null;
    }

    /**
     * @param ObjectType $type
     * @param string     $interfaceName
     * @return NamedTypeNode[]
     */
    protected function getAllImplementsInterfaceNodes(ObjectType $type, string $interfaceName): array
    {
        $nodes = [];

        foreach ($this->getAllObjectOrInterfaceNodes($type) as $object) {
            foreach ($object->getInterfaces() as $node) {
                if ($node->getNameValue() === $interfaceName) {
                    $nodes[] = $node;
                }
            }
        }

        return $nodes;
    }

    /**
     * @param FieldsAwareInterface $type
     * @param string               $fieldName
     * @param string               $argumentName
     * @return TypeNodeInterface|null
     */
    protected function getFieldArgumentTypeNode(
        FieldsAwareInterface $type,
        string $fieldName,
        string $argumentName
    ): ?TypeNodeInterface {
        $node = $this->getFieldArgumentNode($type, $fieldName, $argumentName);
        return null !== $node ? $node->getType() : null;
    }

    /**
     * @param UnionType $unionType
     * @param string    $memberTypeName
     * @return array|null
     */
    protected function getUnionMemberTypeNodes(UnionType $unionType, string $memberTypeName): ?array
    {
        /** @var UnionTypeDefinitionNode|null $node */
        $node = $unionType->getAstNode();

        if (null === $node) {
            return null;
        }

        return \array_filter($node->getTypes(), function (NamedTypeNode $type) use ($memberTypeName) {
            return $type->getNameValue() === $memberTypeName;
        });
    }

    /**
     * @param EnumType $enumType
     * @param string   $valueName
     * @return array|null
     */
    protected function getEnumValueNodes(EnumType $enumType, string $valueName): ?array
    {
        /** @var EnumTypeDefinitionNode|null $node */
        $node = $enumType->getAstNode();

        if (null === $node) {
            return null;
        }

        return \array_filter($node->getValues(), function (NameAwareInterface $type) use ($valueName) {
            return $type->getNameValue() === $valueName;
        });
    }

    /**
     * @param mixed $node
     */
    protected function validateName($node): void
    {
        // Ensure names are valid, however introspection types opt out.
        $error = NameHelper::isValidError($node->getName(), $node);

        if (null !== $error) {
            $this->context->reportError($error);
        }
    }
}
