<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\GraphQLException;
use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\ArgumentsAwareInterface;
use Digia\GraphQL\Language\Node\NameAwareInterface;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NullValueNode;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\Node\VariableNode;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Type\Coercer\CoercingException;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\EnumValue;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\WrappingTypeInterface;
use Digia\GraphQL\Util\ConversionException;
use Digia\GraphQL\Util\TypeASTConverter;
use Digia\GraphQL\Util\ValueASTConverter;
use function Digia\GraphQL\Test\jsonEncode;
use function Digia\GraphQL\Util\find;
use function Digia\GraphQL\Util\invariant;
use function Digia\GraphQL\Util\keyMap;
use function Digia\GraphQL\Util\suggestionList;

class ValuesResolver
{
    /**
     * Prepares an object map of argument values given a list of argument
     * definitions and list of argument AST nodes.
     *
     * @see https://facebook.github.io/graphql/October2016/#CoerceArgumentValues()
     *
     * @param Field|Directive         $definition
     * @param ArgumentsAwareInterface $node
     * @param array                   $variableValues
     * @return array
     * @throws ExecutionException
     * @throws InvariantException
     */
    public static function coerceArgumentValues(
        $definition,
        ArgumentsAwareInterface $node,
        array $variableValues = []
    ): array {
        $coercedValues       = [];
        $argumentDefinitions = $definition->getArguments();
        $argumentNodes       = $node->getArguments();

        if (empty($argumentDefinitions)) {
            return $coercedValues;
        }

        /** @var ArgumentNode[] $argumentNodeMap */
        $argumentNodeMap = keyMap($argumentNodes, function (ArgumentNode $value) {
            return $value->getNameValue();
        });

        foreach ($argumentDefinitions as $argumentDefinition) {
            $argumentName  = $argumentDefinition->getName();
            $argumentType  = $argumentDefinition->getType();
            $argumentNode  = $argumentNodeMap[$argumentName] ?? null;
            $defaultValue  = $argumentDefinition->getDefaultValue();
            $argumentValue = null !== $argumentNode ? $argumentNode->getValue() : null;

            if (null !== $argumentNode && $argumentValue instanceof VariableNode) {
                $variableName = $argumentValue->getNameValue();
                $hasValue     = !empty($variableValues) && \array_key_exists($variableName, $variableValues);
                $isNull       = $hasValue && null === $variableValues[$variableName];
            } else {
                $hasValue = null !== $argumentNode;
                $isNull   = $hasValue && $argumentValue instanceof NullValueNode;
            }

            if (!$hasValue && null !== $defaultValue) {
                // If no argument was provided where the definition has a default value,
                // use the default value.
                $coercedValues[$argumentName] = $defaultValue;
            } elseif ((!$hasValue || $isNull) && $argumentType instanceof NonNullType) {
                // If no argument or a null value was provided to an argument with a
                // non-null type (required), produce a field error.
                if ($isNull) {
                    throw new ExecutionException(
                        \sprintf(
                            'Argument "%s" of non-null type "%s" must not be null.',
                            $argumentName,
                            $argumentType
                        ),
                        [$argumentValue]
                    );
                } elseif (null !== $argumentNode && $argumentValue instanceof VariableNode) {
                    $variableName = $argumentValue->getNameValue();
                    throw new ExecutionException(
                        \sprintf(
                            'Argument "%s" of required type "%s" was provided the variable "$%s" '
                            . 'which was not provided a runtime value.',
                            $argumentName,
                            $argumentType,
                            $variableName
                        ),
                        [$argumentValue]
                    );
                } else {
                    throw new ExecutionException(
                        \sprintf(
                            'Argument "%s" of required type "%s" was not provided.',
                            $argumentName,
                            $argumentType
                        ),
                        [$node]
                    );
                }
            } elseif ($hasValue) {
                if ($argumentValue instanceof NullValueNode) {
                    // If the explicit value `null` was provided, an entry in the coerced
                    // values must exist as the value `null`.
                    $coercedValues[$argumentName] = null;
                } elseif ($argumentValue instanceof VariableNode) {
                    $variableName = $argumentValue->getNameValue();
                    invariant(!empty($variableValues), 'Must exist for hasValue to be true.');
                    // Note: This does no further checking that this variable is correct.
                    // This assumes that this query has been validated and the variable
                    // usage here is of the correct type.
                    $coercedValues[$argumentName] = $variableValues[$variableName];
                } else {
                    $valueNode = $argumentNode->getValue();
                    try {
                        // Value nodes that cannot be resolved should be treated as invalid values
                        // because there is no undefined value in PHP so that we throw an exception
                        $coercedValue = ValueASTConverter::convert($valueNode, $argumentType, $variableValues);
                    } catch (\Exception $ex) {
                        // Note: ValuesOfCorrectType validation should catch this before
                        // execution. This is a runtime check to ensure execution does not
                        // continue with an invalid argument value.
                        throw new ExecutionException(
                            \sprintf(
                                'Argument "%s" has invalid value %s.',
                                $argumentName,
                                (string)$argumentValue
                            ),
                            [$argumentValue],
                            null,
                            null,
                            null,
                            null,
                            $ex
                        );
                    }
                    $coercedValues[$argumentName] = $coercedValue;
                }
            }
        }

        return $coercedValues;
    }

    /**
     * Prepares an object map of argument values given a directive definition
     * and a AST node which may contain directives. Optionally also accepts a map
     * of variable values.
     *
     * If the directive does not exist on the node, returns null.
     *
     * @param Directive $directive
     * @param mixed     $node
     * @param array     $variableValues
     * @return array|null
     * @throws ExecutionException
     * @throws InvariantException
     */
    public static function coerceDirectiveValues(
        Directive $directive,
        $node,
        array $variableValues = []
    ): ?array {
        $directiveNode = $node->hasDirectives()
            ? find($node->getDirectives(), function (NameAwareInterface $value) use ($directive) {
                return $value->getNameValue() === $directive->getName();
            }) : null;

        if (null !== $directiveNode) {
            return static::coerceArgumentValues($directive, $directiveNode, $variableValues);
        }

        return null;
    }

    /**
     * Prepares an object map of variableValues of the correct type based on the
     * provided variable definitions and arbitrary input. If the input cannot be
     * parsed to match the variable definitions, a GraphQLError will be thrown.
     *
     * @param Schema                         $schema
     * @param array|VariableDefinitionNode[] $variableDefinitionNodes
     * @param array                          $inputs
     * @return CoercedValue
     * @throws GraphQLException
     * @throws InvariantException
     * @throws ConversionException
     */
    public static function coerceVariableValues(
        Schema $schema,
        array $variableDefinitionNodes,
        array $inputs
    ): CoercedValue {
        $coercedValues = [];
        $errors        = [];

        foreach ($variableDefinitionNodes as $variableDefinitionNode) {
            $variableName     = $variableDefinitionNode->getVariable()->getNameValue();
            $variableType     = TypeASTConverter::convert($schema, $variableDefinitionNode->getType());
            $variableTypeName = (string)$variableType;

            if ($variableTypeName === '') {
                $variableTypeName = (string)$variableDefinitionNode;
            }

            if (!static::isInputType($variableType)) {
                // Must use input types for variables. This should be caught during
                // validation, however is checked again here for safety.
                $errors[] = static::buildCoerceException(
                    \sprintf(
                        'Variable "$%s" expected value of type "%s" which cannot be used as an input type',
                        $variableName,
                        $variableTypeName
                    ),
                    $variableDefinitionNode,
                    null
                );
            } else {
                $hasValue = \array_key_exists($variableName, $inputs);
                $value    = $hasValue ? $inputs[$variableName] : null;
                if (!$hasValue && $variableDefinitionNode->hasDefaultValue()) {
                    // If no value was provided to a variable with a default value,
                    // use the default value.
                    $coercedValues[$variableName] = ValueASTConverter::convert(
                        $variableDefinitionNode->getDefaultValue(),
                        $variableType
                    );
                } elseif ((!$hasValue || null === $value) && $variableType instanceof NonNullType) {
                    // If no value or a nullish value was provided to a variable with a
                    // non-null type (required), produce an error.
                    $errors[] = static::buildCoerceException(
                        \sprintf(
                            $value
                                ? 'Variable "$%s" of non-null type "%s" must not be null'
                                : 'Variable "$%s" of required type "%s" was not provided',
                            $variableName,
                            $variableTypeName
                        ),
                        $variableDefinitionNode,
                        null
                    );
                } elseif ($hasValue) {
                    if (null === $value) {
                        // If the explicit value `null` was provided, an entry in the coerced
                        // values must exist as the value `null`.
                        $coercedValues[$variableName] = null;
                    } else {
                        // Otherwise, a non-null value was provided, coerce it to the expected
                        // type or report an error if coercion fails.
                        $coercedValue = static::coerceValue($value, $variableType, $variableDefinitionNode);
                        if ($coercedValue->hasErrors()) {
                            $message = \sprintf(
                                'Variable "$%s" got invalid value %s',
                                $variableName,
                                jsonEncode($value)
                            );
                            foreach ($coercedValue->getErrors() as $error) {
                                $errors[] = static::buildCoerceException(
                                    $message,
                                    $variableDefinitionNode,
                                    null,
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
        }

        return new CoercedValue($coercedValues, $errors);
    }

    /**
     * @param TypeInterface|null $type
     * @return bool
     */
    protected static function isInputType(?TypeInterface $type)
    {
        return ($type instanceof ScalarType) ||
            ($type instanceof EnumType) ||
            ($type instanceof InputObjectType) ||
            (($type instanceof WrappingTypeInterface) && static::isInputType($type->getOfType()));
    }

    /**
     * Returns either a value which is valid for the provided type or a list of
     * encountered coercion errors.
     *
     * @param mixed|array   $value
     * @param mixed         $type
     * @param NodeInterface $blameNode
     * @param Path|null     $path
     * @return CoercedValue
     * @throws GraphQLException
     * @throws InvariantException
     */
    protected static function coerceValue($value, $type, $blameNode, ?Path $path = null): CoercedValue
    {
        if ($type instanceof NonNullType) {
            return static::coerceValueForNonNullType($value, $type, $blameNode, $path);
        }

        if (null === $value) {
            return new CoercedValue(null);
        }

        if ($type instanceof ScalarType) {
            return static::coerceValueForScalarType($value, $type, $blameNode, $path);
        }

        if ($type instanceof EnumType) {
            return static::coerceValueForEnumType($value, $type, $blameNode, $path);
        }

        if ($type instanceof ListType) {
            return static::coerceValueForListType($value, $type, $blameNode, $path);
        }

        if ($type instanceof InputObjectType) {
            return static::coerceValueForInputObjectType($value, $type, $blameNode, $path);
        }

        throw new GraphQLException('Unexpected type.');
    }

    /**
     * @param mixed         $value
     * @param NonNullType   $type
     * @param NodeInterface $blameNode
     * @param Path|null     $path
     * @return CoercedValue
     * @throws GraphQLException
     * @throws InvariantException
     */
    protected static function coerceValueForNonNullType(
        $value,
        NonNullType $type,
        NodeInterface $blameNode,
        ?Path $path
    ): CoercedValue {
        if (null === $value) {
            return new CoercedValue(null, [
                static::buildCoerceException(
                    \sprintf('Expected non-nullable type %s not to be null', (string)$type),
                    $blameNode,
                    $path
                )
            ]);
        }
        return static::coerceValue($value, $type->getOfType(), $blameNode, $path);
    }

    /**
     * Scalars determine if a value is valid via parseValue(), which can
     * throw to indicate failure. If it throws, maintain a reference to
     * the original error.
     *
     * @param mixed         $value
     * @param ScalarType    $type
     * @param NodeInterface $blameNode
     * @param Path|null     $path
     * @return CoercedValue
     */
    protected static function coerceValueForScalarType(
        $value,
        ScalarType $type,
        NodeInterface $blameNode,
        ?Path $path
    ): CoercedValue {
        try {
            $parseResult = $type->parseValue($value);
            if (null === $parseResult) {
                return new CoercedValue(null, [
                    new GraphQLException(\sprintf('Expected type %s', (string)$type))
                ]);
            }
            return new CoercedValue($parseResult);
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (InvalidTypeException|CoercingException $ex) {
            return new CoercedValue(null, [
                static::buildCoerceException(
                    \sprintf('Expected type %s', (string)$type),
                    $blameNode,
                    $path,
                    $ex->getMessage(),
                    $ex
                )
            ]);
        }
    }

    /**
     * @param mixed         $value
     * @param EnumType      $type
     * @param NodeInterface $blameNode
     * @param Path|null     $path
     * @return CoercedValue
     * @throws InvariantException
     */
    protected static function coerceValueForEnumType(
        $value,
        EnumType $type,
        NodeInterface $blameNode,
        ?Path $path
    ): CoercedValue {
        if (\is_string($value) && null !== ($enumValue = $type->getValue($value))) {
            return new CoercedValue($enumValue);
        }

        $suggestions = suggestionList((string)$value, \array_map(function (EnumValue $enumValue) {
            return $enumValue->getName();
        }, $type->getValues()));

        $didYouMean = (!empty($suggestions))
            ? 'did you mean' . \implode(',', $suggestions)
            : null;

        return new CoercedValue(null, [
            static::buildCoerceException(\sprintf('Expected type %s', $type->getName()), $blameNode, $path, $didYouMean)
        ]);
    }

    /**
     * @param mixed           $value
     * @param InputObjectType $type
     * @param NodeInterface   $blameNode
     * @param Path|null       $path
     * @return CoercedValue
     * @throws InvariantException
     * @throws GraphQLException
     */
    protected static function coerceValueForInputObjectType(
        $value,
        InputObjectType $type,
        NodeInterface $blameNode,
        ?Path $path
    ): CoercedValue {
        $errors        = [];
        $coercedValues = [];
        $fields        = $type->getFields();

        // Ensure every defined field is valid.
        foreach ($fields as $field) {
            $fieldType = $field->getType();

            if (!isset($value[$field->getName()])) {
                if (!empty($field->getDefaultValue())) {
                    $coercedValue[$field->getName()] = $field->getDefaultValue();
                } elseif ($fieldType instanceof NonNullType) {
                    $errors[] = new GraphQLException(
                        \sprintf(
                            "Field %s of required type %s! was not provided.",
                            static::printPath(new Path($path, $field->getName())),
                            (string)$fieldType->getOfType()
                        )
                    );
                }
            } else {
                $fieldValue   = $value[$field->getName()];
                $coercedValue = static::coerceValue(
                    $fieldValue,
                    $fieldType,
                    $blameNode,
                    new Path($path, $field->getName())
                );

                if ($coercedValue->hasErrors()) {
                    $errors = \array_merge($errors, $coercedValue->getErrors());
                } elseif (empty($errors)) {
                    $coercedValues[$field->getName()] = $coercedValue->getValue();
                }
            }
        }

        // Ensure every provided field is defined.
        foreach ($value as $fieldName => $fieldValue) {
            if (!isset($fields[$fieldName])) {
                $suggestions = suggestionList($fieldName, \array_keys($fields));
                $didYouMean  = (!empty($suggestions))
                    ? 'did you mean' . \implode(',', $suggestions)
                    : null;

                $errors[] = static::buildCoerceException(
                    \sprintf('Field "%s" is not defined by type %s', $fieldName, $type->getName()),
                    $blameNode,
                    $path,
                    $didYouMean
                );
            }
        }

        return new CoercedValue($coercedValues, $errors);
    }

    /**
     * @param mixed         $value
     * @param ListType      $type
     * @param NodeInterface $blameNode
     * @param Path|null     $path
     * @return CoercedValue
     * @throws GraphQLException
     * @throws InvariantException
     */
    protected static function coerceValueForListType(
        $value,
        ListType $type,
        NodeInterface $blameNode,
        ?Path $path
    ): CoercedValue {
        $itemType = $type->getOfType();

        if (\is_array($value) || $value instanceof \Traversable) {
            $errors        = [];
            $coercedValues = [];

            foreach ($value as $index => $itemValue) {
                $coercedValue = static::coerceValue($itemValue, $itemType, $blameNode, new Path($path, $index));

                if ($coercedValue->hasErrors()) {
                    $errors = \array_merge($errors, $coercedValue->getErrors());
                } else {
                    $coercedValues[] = $coercedValue->getValue();
                }
            }

            return new CoercedValue($coercedValues, $errors);
        }

        // Lists accept a non-list value as a list of one.
        $coercedValue = static::coerceValue($value, $itemType, $blameNode);

        return new CoercedValue([$coercedValue->getValue()], $coercedValue->getErrors());
    }

    /**
     * @param string                $message
     * @param NodeInterface         $blameNode
     * @param Path|null             $path
     * @param null|string           $subMessage
     * @param GraphQLException|null $originalException
     * @return GraphQLException
     */
    protected static function buildCoerceException(
        string $message,
        NodeInterface $blameNode,
        ?Path $path,
        ?string $subMessage = null,
        ?GraphQLException $originalException = null
    ) {
        $stringPath = static::printPath($path);

        return new CoercingException(
            $message .
            (($stringPath !== '') ? ' at ' . $stringPath : $stringPath) .
            (($subMessage !== null) ? '; ' . $subMessage : '.'),
            [$blameNode],
            null,
            null,
            null,
            null,
            $originalException
        );
    }

    /**
     * @param Path|null $path
     * @return string
     */
    protected static function printPath(?Path $path)
    {
        $stringPath  = '';
        $currentPath = $path;

        while ($currentPath !== null) {
            $stringPath = \is_string($currentPath->getKey())
                ? '.' . $currentPath->getKey() . $stringPath
                : '[' . (string)$currentPath->getKey() . ']' . $stringPath;

            $currentPath = $currentPath->getPrevious();
        }

        return !empty($stringPath) ? 'value' . $stringPath : '';
    }
}
