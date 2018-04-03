<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\CoercingException;
use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Error\GraphQLException;
use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\ArgumentsAwareInterface;
use Digia\GraphQL\Language\Node\NameAwareInterface;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\Node\VariableNode;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\EnumValue;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InputTypeInterface;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\WrappingTypeInterface;
use function Digia\GraphQL\Util\find;
use function Digia\GraphQL\Util\keyMap;
use function Digia\GraphQL\Util\suggestionList;
use function Digia\GraphQL\Util\typeFromAST;
use function Digia\GraphQL\Util\valueFromAST;

class ValuesHelper
{
    /**
     * Prepares an object map of argument values given a list of argument
     * definitions and list of argument AST nodes.
     *
     * Note: The returned value is a plain Object with a prototype, since it is
     * exposed to user code. Care should be taken to not pull values from the
     * Object prototype.
     *
     * @see http://facebook.github.io/graphql/October2016/#CoerceArgumentValues()
     *
     * @param Field|Directive         $definition
     * @param ArgumentsAwareInterface $node
     * @param array                   $variableValues
     * @return array
     * @throws ExecutionException
     * @throws InvalidTypeException
     * @throws InvariantException
     */
    public function coerceArgumentValues($definition, ArgumentsAwareInterface $node, array $variableValues = []): array
    {
        $coercedValues       = [];
        $argumentDefinitions = $definition->getArguments();
        $argumentNodes       = $node->getArguments();

        if (empty($argumentDefinitions) || empty($argumentNodes)) {
            return $coercedValues;
        }

        $argumentNodeMap = keyMap($argumentNodes, function (ArgumentNode $value) {
            return $value->getNameValue();
        });

        foreach ($argumentDefinitions as $argumentDefinition) {
            $argumentName = $argumentDefinition->getName();
            $argumentType = $argumentDefinition->getType();
            /** @var ArgumentNode $argumentNode */
            $argumentNode = $argumentNodeMap[$argumentName];
            $defaultValue = $argumentDefinition->getDefaultValue();

            if (null === $argumentNode) {
                if (null === $defaultValue) {
                    $coercedValues[$argumentName] = $defaultValue;
                } elseif (!$argumentType instanceof NonNullType) {
                    throw new ExecutionException(
                        sprintf('Argument "%s" of required type "%s" was not provided.', $argumentName, $argumentType),
                        [$node]
                    );
                }
            } elseif ($argumentNode instanceof VariableNode) {
                $coercedValues[$argumentName] = $this->coerceValueForVariableNode(
                    $argumentNode,
                    $argumentType,
                    $argumentName,
                    $variableValues,
                    $defaultValue
                );
            } else {
                $coercedValue = null;

                try {
                    $coercedValue = valueFromAST($argumentNode->getValue(), $argumentType, $variableValues);
                } catch (CoercingException $ex) {
                    // Value nodes that cannot be resolved should be treated as invalid values
                    // therefore we catch the exception and leave the `$coercedValue` as `null`.
                }

                if (null === $coercedValue) {
                    // Note: ValuesOfCorrectType validation should catch this before
                    // execution. This is a runtime check to ensure execution does not
                    // continue with an invalid argument value.
                    throw new ExecutionException(
                        sprintf('Argument "%s" has invalid value %s.', $argumentName, $argumentNode),
                        [$argumentNode->getValue()]
                    );
                }

                $coercedValues[$argumentName] = $coercedValue;
            }
        }

        return $coercedValues;
    }

    /**
     * Prepares an object map of argument values given a directive definition
     * and a AST node which may contain directives. Optionally also accepts a map
     * of variable values.
     *
     * If the directive does not exist on the node, returns undefined.
     *
     * Note: The returned value is a plain Object with a prototype, since it is
     * exposed to user code. Care should be taken to not pull values from the
     * Object prototype.
     *
     * @param Directive $directive
     * @param mixed     $node
     * @param array     $variableValues
     * @return array|null
     * @throws ExecutionException
     * @throws InvalidTypeException
     * @throws InvariantException
     */
    public function coerceDirectiveValues(
        Directive $directive,
        $node,
        array $variableValues = []
    ): ?array {
        $directiveNode = $node->hasDirectives()
            ? find($node->getDirectives(), function (NameAwareInterface $value) use ($directive) {
                return $value->getNameValue() === $directive->getName();
            }) : null;

        if (null !== $directiveNode) {
            return $this->coerceArgumentValues($directive, $directiveNode, $variableValues);
        }

        return null;
    }

    /**
     * @param Schema                         $schema
     * @param array|VariableDefinitionNode[] $variableDefinitionNodes
     * @param                                $input
     * @return array
     * @throws \Exception
     */
    public function coerceVariableValues(Schema $schema, array $variableDefinitionNodes, array $inputs): array
    {
        $coercedValues = [];
        $errors        = [];

        foreach ($variableDefinitionNodes as $variableDefinitionNode) {
            $variableName = $variableDefinitionNode->getVariable()->getNameValue();
            $variableType = typeFromAST($schema, $variableDefinitionNode->getType());

            $type = $variableType;
            if ($variableType instanceof WrappingTypeInterface) {
                $type = $variableType->getOfType();
            }

            //!isInputType(varType)
            if (!$type instanceof InputTypeInterface) {
                throw new GraphQLException('InputTypeInterface');
            } else {
                if (!isset($inputs[$variableName])) {
                    if ($variableType instanceof NonNullType) {
                        throw new GraphQLException('NonNullType');
                    } elseif ($variableDefinitionNode->getDefaultValue() !== null) {
                        $coercedValues[$variableName] = valueFromAST(
                            $variableDefinitionNode->getDefaultValue(),
                            $variableType
                        );
                    }
                } else {
                    $value   = $inputs[$variableName];
                    $coerced = $this->coerceValue($value, $variableType, $variableDefinitionNode);
                    if (!empty($coerced['errors'])) {
                        $messagePrelude = sprintf(
                            'Variable "%s" got invalid value %s',
                            $variableName, json_encode
                            ($value)
                        );
                    } else {
                        $coercedValues[$variableName] = $coerced['coerced'];
                    }
                }
            }
        }

        return [
            'errors'  => $errors ?? [],
            'coerced' => $coercedValues ?? []
        ];
    }

    /**
     * @param       $value
     * @param       $type
     * @param       $blameNode
     * @param array $path
     * @return array
     * @throws GraphQLException
     * @throws InvariantException
     */
    private function coerceValue($value, $type, $blameNode, ?array $path = [])
    {
        if ($type instanceof NonNullType) {
            if (empty($value)) {
                throw new GraphQLException(
                    sprintf('Expected non-nullable type %s not to be null', (string)$type),
                    $blameNode,
                    $path
                );
            }
            return $this->coerceValue($value, $type->getOfType(), $blameNode, $path);
        }

        if (empty($value)) {
            return ['value' => null, 'errors' => null];
        }

        if ($type instanceof ScalarType) {
            try {
                $parseResult = $type->parseValue($value);
                if (empty($parseResult)) {
                    return [
                        'errors'  => new GraphQLException(sprintf('Expected type %s', $type->getName())),
                        'coerced' => null
                    ];
                }
                return [
                    'errors'  => null,
                    'coerced' => $parseResult
                ];
            } catch (\Exception $ex) {
                return [
                    'errors'  => new GraphQLException(sprintf('Expected type %s', $type->getName())),
                    'coerced' => null
                ];
            }
        }

        if ($type instanceof EnumType) {
            if (gettype($value) === 'string') {
                $enumValue = $type->getValue($value);
                if ($enumValue !== null) {
                    return [
                        'value'  => $enumValue,
                        'errors' => null
                    ];
                }
            }
            $suggestions = suggestionList((string)$value, array_map(function (EnumValue $enumValue) {
                return $enumValue->getName();
            }, $type->getValues()));
        }

        if ($type instanceof ListType) {
            $itemType = $type->getOfType();
            if (is_array($value) || $value instanceof \Traversable) {
                $errors       = [];
                $coercedValue = [];
                foreach ($value as $index => $itemValue) {
                    $coercedItem = $this->coerceValue(
                        $itemValue,
                        $itemType,
                        $blameNode,
                        [$path, $index]
                    );

                    if (!empty($coercedItem['errors'])) {
                        $errors = array_merge($errors, $coercedItem['errors']);
                    } else {
                        $coercedValue[] = $coercedItem['coerced'];
                    }
                }

                return [
                    'errors'  => $errors ?? [],
                    'coerced' => $coercedValue ?? []
                ];
            }
        }

        if ($type instanceof InputObjectType) {
            $errors       = [];
            $coercedValue = [];
            $fields       = $type->getFields();

            // Ensure every defined field is valid.
            foreach ($fields as $field) {
                if (!isset($value[$field->getName()])) {
                    if (!empty($field->getDefaultValue())) {
                        $coercedValue[$field->getName()] = $field->getDefaultValue();
                    } elseif($type instanceof NonNullType) {
                        //@TODO proper message
                        $errors[] = new GraphQLException("Field `path` of required type `type` was not provided");
                    }
                } else {
                    $fieldValue   = $value[$field->getName()];
                    $coercedField = $this->coerceValue(
                        $fieldValue,
                        $field->getType(),
                        $blameNode,
                        [$path, $field->getName()] // new path
                    );

                    if ($coercedField['errors']) {
                        $errors = array_merge($errors, $coercedField['errors']);
                    } elseif (empty($errors)) {
                        $coercedValue[$field->getName()] = $coercedField['coerced'];
                    }
                }
            }

            // Ensure every provided field is defined.
            foreach ($value as $fieldName => $value) {
                if ($fields[$fieldName] === null) {
                    $suggestion = suggestionList($fieldName, array_keys($fields));
                    $errors[]   = new GraphQLException(sprintf('Field "%" is not defined by type %s'),
                        $fieldName, $type->getName());
                }
            }

            return [
                'errors'  => $errors ?? [],
                'coerced' => $coercedValue ?? []
            ];
        }

//        if ($type instanceof LeafTypeInterface) {
//            $parsed = $type->parseValue($value);
//            if (null === $parsed) {
//                throw new GraphQLException('Undefined');
//            }
//            return $parsed;
//        }

        throw new GraphQLException('Unexpected type.');
    }

    /**
     * @param VariableNode  $variableNode
     * @param TypeInterface $argumentType
     * @param string        $argumentName
     * @param array         $variableValues
     * @param mixed         $defaultValue
     * @return mixed
     * @throws ExecutionException
     */
    protected function coerceValueForVariableNode(
        VariableNode $variableNode,
        TypeInterface $argumentType,
        string $argumentName,
        array $variableValues,
        $defaultValue
    ) {
        $variableName = $variableNode->getNameValue();

        if (!empty($variableValues) && isset($variableValues[$variableName])) {
            // Note: this does not check that this variable value is correct.
            // This assumes that this query has been validated and the variable
            // usage here is of the correct type.
            return $variableValues[$variableName];
        }

        if (null !== $defaultValue) {
            return $defaultValue;
        }

        if ($argumentType instanceof NonNullType) {
            throw new ExecutionException(
                \sprintf(
                    'Argument "%s" of required type "%s" was provided the variable "%s" which was not provided a runtime value.',
                    $argumentName,
                    $argumentType,
                    $variableName
                ),
                [$variableNode->getValue()]
            );
        }
    }
}
