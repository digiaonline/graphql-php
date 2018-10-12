<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\Node\VariableNode;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Util\TypeASTConverter;
use Digia\GraphQL\Util\TypeHelper;
use Digia\GraphQL\Validation\ValidationException;
use function Digia\GraphQL\Type\newNonNull;
use function Digia\GraphQL\Validation\badVariablePositionMessage;

/**
 * Variables in allowed position
 *
 * Variables passed to field arguments conform to type.
 */
class VariablesInAllowedPositionRule extends AbstractRule
{
    /**
     * @var VariableDefinitionNode[]|null
     */
    protected $variableDefinitionMap;

    /**
     * @inheritdoc
     */
    protected function enterOperationDefinition(OperationDefinitionNode $node): VisitorResult
    {
        $this->variableDefinitionMap = [];

        return new VisitorResult($node);
    }

    /**
     * @inheritdoc
     *
     * @throws InvariantException
     * @throws \Digia\GraphQL\Error\ConversionException
     */
    protected function leaveOperationDefinition(OperationDefinitionNode $node): VisitorResult
    {
        $usages = $this->context->getRecursiveVariableUsages($node);

        /**
         * @var VariableNode  $variableNode
         * @var TypeInterface $type
         */
        foreach ($usages as ['node' => $variableNode, 'type' => $type]) {
            $variableName       = $variableNode->getNameValue();
            $variableDefinition = $this->variableDefinitionMap[$variableName];

            if (null !== $variableDefinition && null !== $type) {
                // A var type is allowed if it is the same or more strict (e.g. is
                // a subtype of) than the expected type. It can be more strict if
                // the variable type is non-null when the expected type is nullable.
                // If both are list types, the variable item type can be more strict
                // than the expected item type (contravariant).
                $schema       = $this->context->getSchema();
                $variableType = TypeASTConverter::convert($schema, $variableDefinition->getType());

                if (null !== $variableType &&
                    !TypeHelper::isTypeSubtypeOf(
                        $schema,
                        $this->getEffectiveType($variableType, $variableDefinition),
                        $type
                    )
                ) {
                    $this->context->reportError(
                        new ValidationException(
                            badVariablePositionMessage($variableName, (string)$variableType, (string)$type),
                            [$variableDefinition, $variableNode]
                        )
                    );
                }
            }
        }

        return new VisitorResult($node);
    }

    /**
     * @inheritdoc
     */
    protected function enterVariableDefinition(VariableDefinitionNode $node): VisitorResult
    {
        $this->variableDefinitionMap[$node->getVariable()->getNameValue()] = $node;

        return new VisitorResult($node);
    }

    /**
     * If a variable definition has a default value, it's effectively non-null.
     *
     * @param TypeInterface          $variableType
     * @param VariableDefinitionNode $variableDefinition
     * @return TypeInterface
     *
     * @throws InvariantException
     */
    protected function getEffectiveType(
        TypeInterface $variableType,
        VariableDefinitionNode $variableDefinition
    ): TypeInterface {
        return (!$variableDefinition->hasDefaultValue() || $variableType instanceof NonNullType)
            ? $variableType
            : newNonNull($variableType);
    }
}
