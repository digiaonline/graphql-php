<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\Node\VariableNode;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Util\TypeComparator;
use function Digia\GraphQL\Type\GraphQLNonNull;
use function Digia\GraphQL\Util\typeFromAST;
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
     * @var TypeComparator
     */
    protected $typeComparator;

    /**
     * VariablesInAllowedPositionRule constructor.
     */
    public function __construct()
    {
        $this->typeComparator = new TypeComparator();
    }


    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof OperationDefinitionNode) {
            $this->variableDefinitionMap = [];
        }

        return $node;
    }

    /**
     * @inheritdoc
     * @throws InvalidTypeException
     */
    public function leaveNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof OperationDefinitionNode) {
            $usages = $this->validationContext->getRecursiveVariableUsages($node);

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
                    $schema       = $this->validationContext->getSchema();
                    $variableType = typeFromAST($schema, $variableDefinition->getType());

                    if (null !== $variableType &&
                        !$this->typeComparator->isTypeSubTypeOf($schema,
                            $this->getEffectiveType($variableType, $variableDefinition), $type)) {
                        $this->validationContext->reportError(
                            new ValidationException(
                                badVariablePositionMessage($variableName, $variableType, $type),
                                [$variableDefinition, $variableNode]
                            )
                        );
                    }
                }
            }
        }

        if ($node instanceof VariableDefinitionNode) {
            $this->variableDefinitionMap[$node->getVariable()->getNameValue()] = $node;
        }

        return $node;
    }

    /**
     * If a variable definition has a default value, it's effectively non-null.
     *
     * @param TypeInterface          $variableType
     * @param VariableDefinitionNode $variableDefinition
     * @return TypeInterface
     * @throws InvalidTypeException
     */
    protected function getEffectiveType(
        TypeInterface $variableType,
        VariableDefinitionNode $variableDefinition
    ): TypeInterface {
        return (!$variableDefinition->hasDefaultValue() || $variableType instanceof NonNullType)
            ? $variableType
            : GraphQLNonNull($variableType);
    }
}
