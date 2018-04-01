<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\CoercingException;
use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\ArgumentsAwareInterface;
use Digia\GraphQL\Language\Node\NameAwareInterface;
use Digia\GraphQL\Language\Node\VariableNode;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use function Digia\GraphQL\Util\find;
use function Digia\GraphQL\Util\keyMap;
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
