<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\CoercingException;
use Digia\GraphQL\Error\ConversionException;
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
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\WrappingTypeInterface;
use Digia\GraphQL\Util\TypeASTConverter;
use Digia\GraphQL\Util\ValueASTConverter;
use function Digia\GraphQL\Util\find;
use function Digia\GraphQL\Util\keyMap;
use function Digia\GraphQL\Util\suggestionList;

class ValuesHelper
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
     * @throws InvalidTypeException
     * @throws InvariantException
     */
    public function coerceArgumentValues($definition, ArgumentsAwareInterface $node, array $variableValues = []): array
    {
        $coercedValues       = [];
        $argumentDefinitions = $definition->getArguments();
        $argumentNodes       = $node->getArguments();

        if (empty($argumentDefinitions)) {
            return $coercedValues;
        }

        $argumentNodeMap = keyMap($argumentNodes, function (ArgumentNode $value) {
            return $value->getNameValue();
        });

        foreach ($argumentDefinitions as $argumentDefinition) {
            $argumentName = $argumentDefinition->getName();
            $argumentType = $argumentDefinition->getType();
            /** @var ArgumentNode|null $argumentNode */
            $argumentNode = $argumentNodeMap[$argumentName] ?? null;
            $defaultValue = $argumentDefinition->getDefaultValue();

            if (null === $argumentNode) {
                if (null !== $defaultValue) {
                    $coercedValues[$argumentName] = $defaultValue;
                } elseif ($argumentType instanceof NonNullType) {
                    throw new ExecutionException(
                        sprintf('Argument "%s" of required type "%s" was not provided.', $argumentName,
                            $argumentType),
                        [$node]
                    );
                }
            } elseif ($argumentNode->getValue() instanceof VariableNode) {
                $coercedValues[$argumentName] = $this->coerceValueForVariableNode(
                    $argumentNode->getValue(),
                    $argumentType,
                    $argumentName,
                    $variableValues,
                    $defaultValue
                );
            } else {
                try {
                    $coercedValues[$argumentName] = ValueASTConverter::convert($argumentNode->getValue(),
                        $argumentType,
                        $variableValues);
                } catch (\Exception $ex) {
                    // Value nodes that cannot be resolved should be treated as invalid values
                    // because there is no undefined value in PHP so that we throw an exception

                    throw new ExecutionException(
                        sprintf('Argument "%s" has invalid value %s.',
                            $argumentName,
                            (string)$argumentNode->getValue()),
                        [$argumentNode->getValue()],
                        null, null, null, $ex
                    );
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
    public function coerceVariableValues(Schema $schema, array $variableDefinitionNodes, array $inputs): CoercedValue
    {
        $coercedValues = [];
        $errors        = [];

        foreach ($variableDefinitionNodes as $variableDefinitionNode) {
            $variableName = $variableDefinitionNode->getVariable()->getNameValue();
            $variableType = TypeASTConverter::convert($schema, $variableDefinitionNode->getType());

            if (!$this->isInputType($variableType)) {
                $variableTypeName = (string)$variableType;

                if ($variableTypeName === '') {
                    $variableTypeName = (string)$variableDefinitionNode;
                }

                $errors[] = $this->buildCoerceException(
                    sprintf(
                        'Variable "$%s" expected value of type "%s" which cannot be used as an input type',
                        $variableName,
                        $variableTypeName
                    ),
                    $variableDefinitionNode,
                    null
                );
            } else {
                if (!array_key_exists($variableName, $inputs)) {
                    if ($variableType instanceof NonNullType) {
                        $errors[] = $this->buildCoerceException(
                            sprintf(
                                'Variable "$%s" of required type "%s" was not provided',
                                $variableName,
                                (string)$variableType
                            ),
                            $variableDefinitionNode,
                            null
                        );
                    } elseif ($variableDefinitionNode->getDefaultValue() !== null) {
                        $coercedValues[$variableName] = ValueASTConverter::convert(
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
                            $variableName, json_encode($value)
                        );
                        foreach ($coercedValue->getErrors() as $error) {
                            $errors[] = $this->buildCoerceException(
                                $messagePrelude,
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

        return new CoercedValue($coercedValues, $errors);
    }


    /**
     * @param TypeInterface|null $type
     * @return bool
     */
    protected function isInputType(?TypeInterface $type)
    {
        return ($type instanceof ScalarType) ||
            ($type instanceof EnumType) ||
            ($type instanceof InputObjectType) ||
            (($type instanceof WrappingTypeInterface) && $this->isInputType($type->getOfType()));
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
    private function coerceValue($value, $type, $blameNode, ?Path $path = null): CoercedValue
    {
        if ($type instanceof NonNullType) {
            return $this->coerceValueForNonNullType($value, $type, $blameNode, $path);
        }

        if (empty($value)) {
            return new CoercedValue(null, null);
        }

        if ($type instanceof ScalarType) {
            return $this->coerceValueForScalarType($value, $type, $blameNode, $path);
        }

        if ($type instanceof EnumType) {
            return $this->coerceValueForEnumType($value, $type, $blameNode, $path);
        }

        if ($type instanceof ListType) {
            return $this->coerceValueForListType($value, $type, $blameNode, $path);
        }

        if ($type instanceof InputObjectType) {
            return $this->coerceValueForInputObjectType($value, $type, $blameNode, $path);
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
    protected function coerceValueForNonNullType(
        $value,
        NonNullType $type,
        NodeInterface $blameNode,
        ?Path $path
    ): CoercedValue {
        if (null === $value) {
            return new CoercedValue(null, [
                $this->buildCoerceException(
                    sprintf('Expected non-nullable type %s not to be null', (string)$type),
                    $blameNode,
                    $path
                )
            ]);
        }
        return $this->coerceValue($value, $type->getOfType(), $blameNode, $path);
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
    protected function coerceValueForScalarType(
        $value,
        ScalarType $type,
        NodeInterface $blameNode,
        ?Path $path
    ): CoercedValue {
        try {
            $parseResult = $type->parseValue($value);
            if (empty($parseResult)) {
                return new CoercedValue(null, [
                    new GraphQLException(sprintf('Expected type %s', (string)$type))
                ]);
            }
            return new CoercedValue($parseResult, null);
        } catch (InvalidTypeException|CoercingException $ex) {
            return new CoercedValue(null, [
                $this->buildCoerceException(
                    sprintf('Expected type %s', (string)$type),
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
    protected function coerceValueForEnumType(
        $value,
        EnumType $type,
        NodeInterface $blameNode,
        ?Path $path
    ): CoercedValue {
        if (\is_string($value) && null !== ($enumValue = $type->getValue($value))) {
            return new CoercedValue($enumValue, null);
        }

        $suggestions = suggestionList((string)$value, \array_map(function (EnumValue $enumValue) {
            return $enumValue->getName();
        }, $type->getValues()));

        $didYouMean = (!empty($suggestions))
            ? 'did you mean' . \implode(',', $suggestions)
            : null;

        return new CoercedValue(null, [
            $this->buildCoerceException(\sprintf('Expected type %s', $type->getName()), $blameNode, $path, $didYouMean)
        ]);
    }

    /**
     * @param mixed           $value
     * @param InputObjectType $type
     * @param NodeInterface   $blameNode
     * @param Path|null       $path
     * @return CoercedValue
     * @throws GraphQLException
     * @throws InvariantException
     */
    protected function coerceValueForInputObjectType(
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

            if (empty($value[$field->getName()])) {
                if (!empty($field->getDefaultValue())) {
                    $coercedValue[$field->getName()] = $field->getDefaultValue();
                } elseif ($fieldType instanceof NonNullType) {
                    $errors[] = new GraphQLException(
                        \sprintf(
                            "Field %s of required type %s! was not provided.",
                            $this->printPath(new Path($path, $field->getName())),
                            (string)$fieldType->getOfType()
                        )
                    );
                }
            } else {
                $fieldValue   = $value[$field->getName()];
                $coercedValue = $this->coerceValue(
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

                $errors[] = $this->buildCoerceException(
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
    protected function coerceValueForListType(
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
                $coercedValue = $this->coerceValue($itemValue, $itemType, $blameNode, new Path($path, $index));

                if ($coercedValue->hasErrors()) {
                    $errors = \array_merge($errors, $coercedValue->getErrors());
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

    /**
     * @param string                $message
     * @param NodeInterface         $blameNode
     * @param Path|null             $path
     * @param null|string           $subMessage
     * @param GraphQLException|null $originalException
     * @return GraphQLException
     */
    protected function buildCoerceException(
        string $message,
        NodeInterface $blameNode,
        ?Path $path,
        ?string $subMessage = null,
        ?GraphQLException $originalException = null
    ) {
        $stringPath = $this->printPath($path);

        return new CoercingException(
            $message .
            (($stringPath !== '') ? ' at ' . $stringPath : $stringPath) .
            (($subMessage !== null) ? '; ' . $subMessage : '.'),
            [$blameNode],
            null,
            null,
            [],
            $originalException
        );
    }

    /**
     * @param Path|null $path
     * @return string
     */
    protected function printPath(?Path $path)
    {
        $stringPath  = '';
        $currentPath = $path;

        while ($currentPath !== null) {
            if (\is_string($currentPath->getKey())) {
                $stringPath = '.' . $currentPath->getKey() . $stringPath;
            } else {
                $stringPath = '[' . (string)$currentPath->getKey() . ']' . $stringPath;
            }

            $currentPath = $currentPath->getPrevious();
        }

        return !empty($stringPath) ? 'value' . $stringPath : '';
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
                    'Argument "%s" of required type "%s" was provided the variable "$%s" which was not provided a runtime value.',
                    $argumentName,
                    $argumentType,
                    $variableName
                ),
                [$variableNode]
            );
        }
    }
}
