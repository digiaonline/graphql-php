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
use Digia\GraphQL\Language\Node\NodeInterface;
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
     * @return CoercedValue
     * @throws \Exception
     */
    public function coerceVariableValues(Schema $schema, array $variableDefinitionNodes, array $inputs): CoercedValue
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
                    $value        = $inputs[$variableName];
                    $coercedValue = $this->coerceValue($value, $variableType, $variableDefinitionNode);
                    if ($coercedValue->hasErrors()) {
                        $messagePrelude = sprintf(
                            'Variable "$%s" got invalid value %s',
                            $variableName, json_encode
                            ($value)
                        );
                        foreach ($coercedValue->getErrors() as $error) {
                            $errors[] = $this->buildCoerceException(
                                $messagePrelude,
                                $variableDefinitionNode,
                                [],
                                $error->getMessage(),
                                $error
                            );
                        }
                    } else {
                        $coercedValues[$variableName] = $coercedValue->getValue();
                    }
                }
            }
        }

        return new CoercedValue($coercedValues, $errors);
    }

    /**
     * @param  mixed|array $value
     * @param              $type
     * @param              $blameNode
     * @param array        $path
     * @return CoercedValue
     * @throws InvariantException
     * @throws GraphQLException
     */
    private function coerceValue($value, $type, $blameNode, ?array $path = null): CoercedValue
    {
        if ($type instanceof NonNullType) {
            if (empty($value)) {
                return new CoercedValue(null, [
                    $this->buildCoerceException(
                        sprintf('Expected non-nullable type %s! not to be null', $type->getOfType()->getName()),
                        $blameNode,
                        $path
                    )
                ]);
            }
            return $this->coerceValue($value, $type->getOfType(), $blameNode, $path);
        }

        if (empty($value)) {
            return new CoercedValue(null, null);
        }

        if ($type instanceof ScalarType) {
            try {
                $parseResult = $type->parseValue($value);
                if (empty($parseResult)) {
                    return new CoercedValue(null, [
                        new GraphQLException(sprintf('Expected type %s', $type->getName()))
                    ]);
                }
                return new CoercedValue($parseResult, null);
            } catch (\Exception $ex) {
                //@TODO Check exception message
                return new CoercedValue(null, [
                    new GraphQLException(sprintf('Expected type %s', $type->getName()))
                ]);
            }
        }

        if ($type instanceof EnumType) {
            if (is_string($value)) {
                $enumValue = $type->getValue($value);
                if ($enumValue !== null) {
                    return new CoercedValue($enumValue, null);
                }
            }
            $suggestions = suggestionList((string)$value, array_map(function (EnumValue $enumValue) {
                return $enumValue->getName();
            }, $type->getValues()));

            //@TODO throw proper error
        }

        if ($type instanceof ListType) {
            $itemType = $type->getOfType();
            if (is_array($value) || $value instanceof \Traversable) {
                $errors        = [];
                $coercedValues = [];
                foreach ($value as $index => $itemValue) {
                    $coercedValue = $this->coerceValue(
                        $itemValue,
                        $itemType,
                        $blameNode,
                        [$path, $index]
                    );

                    if ($coercedValue->hasErrors()) {
                        $errors = array_merge($errors, $coercedValue->getErrors());
                    } else {
                        $coercedValues[] = $coercedValue->getValue();
                    }
                }

                return new CoercedValue($coercedValues, $errors);
            }

            // Lists accept a non-list value as a list of one.
            $coercedValue = $this->coerceValue($value, $itemType, $blameNode);
            return new CoercedValue([$coercedValue->getValue()], $coercedValue->getErrors());
        }

        if ($type instanceof InputObjectType) {
            $errors        = [];
            $coercedValues = [];
            $fields        = $type->getFields();

            // Ensure every defined field is valid.
            foreach ($fields as $field) {
                if (!array_key_exists($field->getName(), $value)) {
                    if (!empty($field->getDefaultValue())) {
                        $coercedValue[$field->getName()] = $field->getDefaultValue();
                    } elseif ($type instanceof NonNullType) {
                        $errors[] = new GraphQLException(
                            sprintf(
                                "Field %s of required type %s was not provided",
                                implode(",", array_merge($path, [$field->getName()])),
                                $type->getName()
                            )
                        );
                    }
                } else {
                    $fieldValue   = $value[$field->getName()];
                    $coercedValue = $this->coerceValue(
                        $fieldValue,
                        $field->getType(),
                        $blameNode,
                        [$path, $field->getName()] // new path
                    );

                    if ($coercedValue->hasErrors()) {
                        $errors = array_merge($errors, $coercedValue->getErrors());
                    } elseif (empty($errors)) {
                        $coercedValues[$field->getName()] = $coercedValue->getValue();
                    }
                }
            }

            // Ensure every provided field is defined.
            foreach ($value as $fieldName => $fieldValue) {
                if ($fields[$fieldName] === null) {
                    $suggestion = suggestionList($fieldName, array_keys($fields));
                    $errors[]   = new GraphQLException(
                        sprintf('Field "%s" is not defined by type %s', $fieldName, $type->getName())
                    );
                }
            }

            return new CoercedValue($coercedValues, $errors);
        }

        throw new GraphQLException('Unexpected type.');
    }

    /**
     * @param string                $message
     * @param NodeInterface         $blameNode
     * @param array|null            $path
     * @param null|string           $subMessage
     * @param GraphQLException|null $originalError
     * @return GraphQLException
     */
    protected function buildCoerceException(
        string $message,
        NodeInterface $blameNode,
        ?array $path,
        ?string $subMessage = null,
        ?GraphQLException $originalError = null
    ) {
        $stringPath = implode(".", $path);

        return new CoercingException(
            $message .
            (($stringPath !== '') ? ' at value' . $stringPath : $stringPath) .
            (($subMessage !== null) ? '; ' . $subMessage : '.'),
            [$blameNode],
            null,
            null,
            [],
            $originalError
        );
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
