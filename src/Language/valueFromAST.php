<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Language\AST\Node\ValueNodeInterface;
use Digia\GraphQL\Language\AST\Node\EnumValueNode;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\ListValueNode;
use Digia\GraphQL\Language\AST\Node\NullValueNode;
use Digia\GraphQL\Language\AST\Node\ObjectFieldNode;
use Digia\GraphQL\Language\AST\Node\ObjectValueNode;
use Digia\GraphQL\Language\AST\Node\VariableNode;
use Digia\GraphQL\Type\Definition\InputTypeInterface;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ScalarType;
use function Digia\GraphQL\Util\keyMap;

/**
 * @param ValueNodeInterface|null          $node
 * @param TypeInterface|InputTypeInterface $type
 * @param array                            $variables
 * @return mixed|null
 * @throws \Exception
 */
function valueFromAST(?ValueNodeInterface $node, TypeInterface $type, array $variables = [])
{
    // TODO: Refactor this method, maybe into a more OOP approach.

    if (null === $node) {
        return; // Invalid: intentionally return no value.
    }

    if ($type instanceof NonNullType) {
        if ($node instanceof NullValueNode) {
            return; // Invalid: intentionally return no value.
        }

        return valueFromAST($node, $type->getOfType(), $variables);
    }

    if ($node instanceof NullValueNode) {
        return null;
    }

    if ($node instanceof VariableNode) {
        $variableName = $node->getNameValue();

        if (!isset($variables[$variableName])) {
            // No valid return value.
            return;
        }

        // Note: we're not doing any checking that this variable is correct. We're
        // assuming that this query has been validated and the variable usage here
        // is of the correct type.
        return $variables[$variableName];
    }

    if ($type instanceof ListType) {
        $itemType = $type->getOfType();

        if ($node instanceof ListValueNode) {
            $coercedValues = [];

            foreach ($node->getValues() as $value) {
                if (isMissingVariable($value, $variables)) {
                    // If an array contains a missing variable, it is either coerced to
                    // null or if the item type is non-null, it considered invalid.
                    if ($itemType instanceof NonNullType) {
                        return; // Invalid: intentionally return no value.
                    }
                    $coercedValues[] = null;
                } else {
                    $itemValue = valueFromAST($value, $itemType, $variables);
                    if (!$itemValue) {
                        return; // Invalid: intentionally return no value.
                    }
                    $coercedValues[] = $itemValue;
                }
            }

            return $coercedValues;
        }

        $coercedValue = valueFromAST($node, $itemType, $variables);
        if (!$coercedValue) {
            return; // Invalid: intentionally return no value.
        }
        return [$coercedValue];
    }

    if ($type instanceof InputObjectType) {
        if (!$node instanceof ObjectValueNode) {
            return; // Invalid: intentionally return no value.
        }

        $coercedObject = [];

        /** @var ObjectFieldNode[] $fieldNodes */
        $fieldNodes = keyMap($node->getFields(), function (FieldNode $value) {
            return $value->getNameValue();
        });

        foreach ($type->getFields() as $field) {
            $name      = $field->getName();
            $fieldNode = $fieldNodes[$name];

            if (null === $fieldNode || isMissingVariable($fieldNode->getValue(), $variables)) {
                if (null !== $field->getDefaultValue()) {
                    $coercedObject[$name] = $field->getDefaultValue();
                } elseif ($field->getType() instanceof NonNullType) {
                    return; // Invalid: intentionally return no value.
                }
                continue;
            }

            $fieldValue = valueFromAST($fieldNode->getValue(), $field->getType(), $variables);

            if (!$fieldValue) {
                return; // Invalid: intentionally return no value.
            }

            $coercedObject[$name] = $fieldValue;
        }

        return $coercedObject;
    }

    if ($type instanceof EnumType) {
        if (!$node instanceof EnumValueNode) {
            return; // Invalid: intentionally return no value.
        }

        $enumValue = $type->getValue($node->getValue());

        if (null === $enumValue) {
            return; // Invalid: intentionally return no value.
        }

        return $enumValue->getValue();
    }

    if ($type instanceof ScalarType) {
        // Scalars fulfill parsing a literal value via parseLiteral().
        // Invalid values represent a failure to parse correctly, in which case
        // no value is returned.
        $result = null;
        try {
            $result = $type->parseLiteral($node, $variables);
        } catch (\Exception $e) {
            return; // Invalid: intentionally return no value.
        }

        if (null === $result) {
            return; // Invalid: intentionally return no value.
        }

        return $result;
    }

    throw new \Exception(sprintf('Unknown type: %s', $type));
}

/**
 * @param ValueNodeInterface $node
 * @param array              $variables
 * @return bool
 */
function isMissingVariable(ValueNodeInterface $node, array $variables): bool
{
    return $node instanceof VariableNode && isset($variables[$node->getNameValue()]);
}
