<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Language\AST\Node\ArgumentNode;
use Digia\GraphQL\Language\AST\Node\DirectivesTrait;
use Digia\GraphQL\Language\AST\Node\DirectiveNode;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\NamedTypeNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\VariableNode;
use Digia\GraphQL\Type\Definition\DirectiveInterface;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\NonNullType;
use function Digia\GraphQL\Language\valueFromAST;
use function Digia\GraphQL\Util\find;
use function Digia\GraphQL\Util\keyMap;

/**
 * @param Field|DirectiveInterface              $definition
 * @param FieldNode|DirectiveNode|NodeInterface $node
 * @param array                                 $variableValues
 * @return array
 * @throws GraphQLError
 * @throws \Exception
 */
function getArgumentValues($definition, NodeInterface $node, array $variableValues = []): array
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
        $name         = $argumentDefinition->getName();
        $argumentType = $argumentDefinition->getType();
        /** @var ArgumentNode $argumentNode */
        $argumentNode = $argumentNodeMap[$name];
        $defaultValue = $argumentDefinition->getDefaultValue();

        if (null === $argumentNode) {
            if (null === $defaultValue) {
                $coercedValues[$name] = $defaultValue;
            } elseif (!$argumentType instanceof NonNullType) {
                throw new GraphQLError(
                    sprintf('Argument "%s" of required type "%s" was not provided.', $name, $argumentType),
                    [$node]
                );
            }
        } elseif ($argumentNode instanceof VariableNode) {
            $variableName = $argumentNode->getNameValue();

            if (!empty($variableValues) && isset($variableValues[$variableName])) {
                // Note: this does not check that this variable value is correct.
                // This assumes that this query has been validated and the variable
                // usage here is of the correct type.
                $coercedValues[$name] = $variableValues[$variableName];
            } elseif (null !== $defaultValue) {
                $coercedValues[$name] = $defaultValue;
            } elseif ($argumentType instanceof NonNullType) {
                throw new GraphQLError(
                    sprintf(
                        'Argument "%s" of required type "%s" was provided the variable "%s" which was not provided a runtime value.',
                        $name,
                        $argumentType,
                        $variableName
                    ),
                    [$argumentNode->getValue()]
                );
            }
        } else {
            $valueNode = $argumentNode->getValue();

            $coercedValue = valueFromAST($valueNode, $argumentType, $variableValues);

            if (null === $coercedValue) {
                // Note: ValuesOfCorrectType validation should catch this before
                // execution. This is a runtime check to ensure execution does not
                // continue with an invalid argument value.
                throw new GraphQLError(
                    sprintf('Argument "%s" has invalid value %s.', $name, $valueNode),
                    [$argumentNode->getValue()]
                );
            }

            $coercedValues[$name] = $coercedValue;
        }
    }

    return $coercedValues;
}

/**
 * @param DirectiveInterface            $directive
 * @param NodeInterface|DirectivesTrait $node
 * @param array                         $variableValues
 * @return array|null
 * @throws GraphQLError
 * @throws \Exception
 */
function getDirectiveValues(DirectiveInterface $directive, NodeInterface $node, array $variableValues = []): ?array
{
    $directiveNode = $node->hasDirectives()
        ? find($node->getDirectives(), function (NamedTypeNode $value) use ($directive) {
            return $value->getNameValue() === $directive->getName();
        }) : null;

    if (null !== $directiveNode) {
        return getArgumentValues($directive, $directiveNode, $variableValues);
    }

    return null;
}
