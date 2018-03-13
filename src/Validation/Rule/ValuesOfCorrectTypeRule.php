<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\BooleanValueNode;
use Digia\GraphQL\Language\Node\EnumValueNode;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\FloatValueNode;
use Digia\GraphQL\Language\Node\IntValueNode;
use Digia\GraphQL\Language\Node\ListValueNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NullValueNode;
use Digia\GraphQL\Language\Node\ObjectFieldNode;
use Digia\GraphQL\Language\Node\ObjectValueNode;
use Digia\GraphQL\Language\Node\StringValueNode;
use Digia\GraphQL\Language\Node\ValueNodeInterface;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\EnumValue;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ScalarType;
use function Digia\GraphQL\printNode;
use function Digia\GraphQL\Type\getNamedType;
use function Digia\GraphQL\Type\getNullableType;
use function Digia\GraphQL\Util\keyMap;
use function Digia\GraphQL\Util\orList;
use function Digia\GraphQL\Util\suggestionList;
use function Digia\GraphQL\Validation\badValueMessage;
use function Digia\GraphQL\Validation\requiredFieldMessage;
use function Digia\GraphQL\Validation\unknownFieldMessage;

/**
 * Value literals of correct type
 *
 * A GraphQL document is only valid if all value literals are of the type
 * expected at their position.
 */
class ValuesOfCorrectTypeRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof NullValueNode) {
            return $this->handleNullValue($node);
        }

        if ($node instanceof ListValueNode) {
            return $this->handleListValue($node);
        }

        if ($node instanceof ObjectValueNode) {
            return $this->handleObjectValue($node);
        }

        if ($node instanceof ObjectFieldNode) {
            return $this->handleObjectField($node);
        }

        if ($node instanceof EnumValueNode) {
            return $this->handleEnumValue($node);
        }

        if ($node instanceof IntValueNode ||
            $node instanceof FloatValueNode ||
            $node instanceof StringValueNode ||
            $node instanceof BooleanValueNode) {
            $this->isValidScalar($node);
        }

        return $node;
    }

    /**
     * @param NullValueNode $node
     * @return NodeInterface|null
     */
    protected function handleNullValue(NullValueNode $node): ?NodeInterface
    {
        $type = $this->validationContext->getInputType();

        if ($type instanceof NonNullType) {
            $this->validationContext->reportError(
                new ValidationException(
                    badValueMessage((string)$type, printNode($node)),
                    [$node]
                )
            );
        }

        return $node;
    }

    /**
     * @param ListValueNode $node
     * @return NodeInterface|null
     * @throws InvariantException
     */
    protected function handleListValue(ListValueNode $node): ?NodeInterface
    {
        $type = getNullableType($this->validationContext->getParentInputType());

        if ($type instanceof ListType) {
            $this->isValidScalar($node);

            return null; // Don't traverse further.
        }

        return $node;
    }

    /**
     * @param ObjectFieldNode $node
     * @throws InvariantException
     */
    protected function handleObjectValue(ObjectValueNode $node): ?NodeInterface
    {
        $type = getNamedType($this->validationContext->getInputType());

        if (!($type instanceof InputObjectType)) {
            $this->isValidScalar($node);

            return null; // Don't traverse further.
        }

        $inputFields  = $type->getFields();
        $fieldNodeMap = keyMap($node->getFields(), function (FieldNode $field) {
            return $field->getNameValue();
        });

        foreach ($inputFields as $fieldName => $field) {
            $fieldType = $field->getType();
            $fieldNode = $fieldNodeMap[$fieldName];

            if (null === $fieldNode && $fieldType instanceof NonNullType) {
                $this->validationContext->reportError(
                    new ValidationException(
                        requiredFieldMessage($type->getName(), $fieldName, (string)$fieldType),
                        [$node]
                    )
                );
            }
        }

        return $node;
    }

    /**
     * @param ObjectFieldNode $node
     * @throws InvariantException
     */
    protected function handleObjectField(ObjectFieldNode $node): ?NodeInterface
    {
        $parentType = getNamedType($this->validationContext->getParentInputType());
        $fieldType  = $this->validationContext->getInputType();

        if (null === $fieldType && $parentType instanceof InputObjectType) {
            $suggestions = suggestionList($node->getNameValue(), \array_keys($parentType->getFields()));
            $didYouMean  = !empty($suggestions) ? sprintf('Did you mean %s?', orList($suggestions)) : null;

            $this->validationContext->reportError(
                new ValidationException(
                    unknownFieldMessage($parentType->getName(), $node->getNameValue(), $didYouMean)
                )
            );
        }

        return $node;
    }

    /**
     * @param EnumValueNode $node
     * @throws InvariantException
     */
    protected function handleEnumValue(EnumValueNode $node): ?NodeInterface
    {
        $type = getNamedType($this->validationContext->getInputType());

        if (!($type instanceof EnumType)) {
            $this->isValidScalar($node);
        } elseif (!$type->getValue($node->getValue())) {
            $didYouMean = $this->getEnumTypeSuggestion($type, $node);

            $this->validationContext->reportError(
                new ValidationException(
                    badValueMessage($type->getName(), printNode($node), $didYouMean),
                    [$node]
                )
            );
        }

        return $node;
    }

    /**
     * Any value literal may be a valid representation of a Scalar, depending on
     * that scalar type.
     *
     * @param ValueNodeInterface $node
     * @throws InvariantException
     */
    protected function isValidScalar(ValueNodeInterface $node): void
    {
        $locationType = $this->validationContext->getInputType();

        if (null === $locationType) {
            return;
        }

        $type = getNamedType($locationType);

        if (!($type instanceof ScalarType)) {
            $didYouMean = $this->getEnumTypeSuggestion($type, $node) ?? null;

            $this->validationContext->reportError(
                new ValidationException(
                    badValueMessage((string)$locationType, printNode($node), $didYouMean),
                    [$node]
                )
            );

            return;
        }

        // Scalars determine if a literal value is valid via parseLiteral() which
        // may throw or return an invalid value to indicate failure.
        try {
            $result = $type->parseLiteral($node, null/* $variables */);

            if (null === $result) {
                $this->validationContext->reportError(
                    new ValidationException(
                        badValueMessage((string)$locationType, printNode($node)),
                        [$node]
                    )
                );
            }
        } catch (\Exception $ex) {
            $this->validationContext->reportError(
                new ValidationException(
                    badValueMessage((string)$locationType, printNode($node), $ex->getMessage()),
                    $node,
                    null,
                    null,
                    null,
                    $ex
                )
            );
        }
    }

    /**
     * @param NamedTypeInterface $type
     * @param ValueNodeInterface $node
     * @return null|string
     * @throws InvariantException
     */
    protected function getEnumTypeSuggestion(NamedTypeInterface $type, ValueNodeInterface $node): ?string
    {
        if (!($type instanceof EnumType)) {
            return null;
        }

        $suggestions = suggestionList(printNode($node), array_map(function (EnumValue $value) {
            return $value->getName();
        }, $type->getValues()));

        return !empty($suggestions) ? sprintf('Did you mean the enum value %s?', orList($suggestions)) : null;
    }
}
