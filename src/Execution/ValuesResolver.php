<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\DirectivesTrait;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\VariableNode;
use Digia\GraphQL\Type\Definition\DirectiveInterface;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use function Digia\GraphQL\Language\valueFromAST;
use function Digia\GraphQL\Util\find;
use function Digia\GraphQL\Util\keyMap;

/**
 * Class ValuesResolver
 * @package Digia\GraphQL\Execution
 */
class ValuesResolver
{
    /**
     * @see http://facebook.github.io/graphql/October2016/#CoerceArgumentValues()
     *
     * @param Field|DirectiveInterface $objectType
     * @param FieldNode                $field
     * @param array                    $variableValues
     * @return array
     * @throws ExecutionException
     * @throws InvalidTypeException
     * @throws InvariantException
     */
    public function coerceArgumentValues($objectType, $field, array $variableValues = []): array
    {
        $coercedValues       = [];
        $argumentDefinitions = $objectType->getArguments();
        $argumentNodes       = $field->getArguments();

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
                        [$field]
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
                $coercedValues[$argumentName] = $this->coerceValueForASTNode(
                    $argumentNode,
                    $argumentType,
                    $argumentName,
                    $variableValues
                );
            }
        }

        return $coercedValues;
    }

    /**
     * @param NodeInterface $argumentNode
     * @param TypeInterface $argumentType
     * @param string        $argumentName
     * @param               $variableValues
     * @return mixed|null
     * @throws ExecutionException
     * @throws InvalidTypeException
     * @throws InvariantException
     */
    protected function coerceValueForASTNode(
        NodeInterface $argumentNode,
        TypeInterface $argumentType,
        string $argumentName,
        $variableValues
    ) {
        $valueNode = $argumentNode->getValue();

        $coercedValue = valueFromAST($valueNode, $argumentType, $variableValues);

        if (null === $coercedValue) {
            // Note: ValuesOfCorrectType validation should catch this before
            // execution. This is a runtime check to ensure execution does not
            // continue with an invalid argument value.
            throw new ExecutionException(
                sprintf('Argument "%s" has invalid value %s.', $argumentName, $valueNode),
                [$argumentNode->getValue()]
            );
        }

        return $coercedValue;
    }

    /**
     * @param VariableNode  $variableNode
     * @param TypeInterface $argumentType
     * @param string        $argumentName
     * @param array         $variableValues
     * @param               $defaultValue
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
                sprintf(
                    'Argument "%s" of required type "%s" was provided the variable "%s" which was not provided a runtime value.',
                    $argumentName,
                    $argumentType,
                    $variableName
                ),
                [$variableNode->getValue()]
            );
        }
    }

    /**
     * @param DirectiveInterface            $directive
     * @param NodeInterface|DirectivesTrait $node
     * @param array                         $variableValues
     * @return array|null
     * @throws ExecutionException
     * @throws InvalidTypeException
     * @throws InvariantException
     */
    public function getDirectiveValues(
        DirectiveInterface $directive,
        NodeInterface $node, array
        $variableValues = []
    ): ?array {
        $directiveNode = $node->hasDirectives()
            ? find($node->getDirectives(), function ($value) use ($directive) {
                return $value->getNameValue() === $directive->getName();
            }) : null;

        if (null !== $directiveNode) {
            return $this->coerceArgumentValues($directive, $directiveNode, $variableValues);
        }

        return null;
    }
}