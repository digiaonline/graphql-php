<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\InterfacesTrait;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Type\Definition\AbstractTypeInterface;
use Digia\GraphQL\Type\Definition\FieldsTrait;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\NameTrait;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\OutputTypeInterface;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\SchemaInterface;
use function Digia\GraphQL\Util\suggestionList;

class FieldOnCorrectTypeRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof FieldNode) {
            $type = $this->context->getParentType();

            if (null !== $type && $type instanceof OutputTypeInterface) {
                $fieldDefinition = $this->context->getFieldDefinition();

                if (null === $fieldDefinition) {
                    $schema              = $this->context->getSchema();
                    $fieldName           = $node->getNameValue();
                    $suggestedTypeNames  = getSuggestedTypeNames($schema, $type, $fieldName);
                    $suggestedFieldNames = count($suggestedTypeNames) !== 0
                        ? []
                        : getSuggestedFieldNames($type, $fieldName);

                    $this->context->reportError(
                        new ValidationException(
                            undefinedFieldMessage(
                                $fieldName,
                                (string)$type,
                                $suggestedTypeNames,
                                $suggestedFieldNames
                            ),
                            [$node]
                        )
                    );
                }
            }
        }

        return $node;
    }
}

/**
 * Go through all of the implementations of type, as well as the interfaces
 * that they implement. If any of those types include the provided field,
 * suggest them, sorted by how often the type is referenced,  starting
 * with Interfaces.
 *
 * @param SchemaInterface $schema
 * @param TypeInterface   $type
 * @param string          $fieldName
 * @return array
 * @throws \Exception
 */
function getSuggestedTypeNames(SchemaInterface $schema, TypeInterface $type, string $fieldName): array
{
    if (!$type instanceof AbstractTypeInterface) {
        // Otherwise, must be an Object type, which does not have possible fields.
        return [];
    }

    $suggestedObjectTypes = [];
    $interfaceUsageCount  = [];

    /** @var FieldsTrait|NameTrait|InterfacesTrait $possibleType */
    foreach ($schema->getPossibleTypes($type) as $possibleType) {
        $typeFields = $possibleType->getFields();
        if (!isset($typeFields[$fieldName])) {
            break;
        }

        $suggestedObjectTypes[] = $possibleType->getName();

        /** @var InterfaceType $possibleInterface */
        foreach ($possibleType->getInterfaces() as $possibleInterface) {
            $interfaceFields = $possibleInterface->getFields();
            if (!isset($interfaceFields[$fieldName])) {
                break;
            }

            $interfaceName                       = $possibleInterface->getName();
            $interfaceUsageCount[$interfaceName] = ($interfaceUsageCount[$interfaceName] ?? 0) + 1;
        }
    }

    $suggestedInterfaceTypes = array_keys($interfaceUsageCount);

    uasort($suggestedInterfaceTypes, function ($a, $b) use ($interfaceUsageCount) {
        return $interfaceUsageCount[$b] - $interfaceUsageCount[$a];
    });

    return array_merge($suggestedInterfaceTypes, $suggestedObjectTypes);
}

/**
 * For the field name provided, determine if there are any similar field names
 * that may be the result of a typo.
 *
 * @param OutputTypeInterface $type
 * @param string              $fieldName
 * @return array
 * @throws \Exception
 */
function getSuggestedFieldNames(OutputTypeInterface $type, string $fieldName): array
{
    if (!($type instanceof ObjectType || $type instanceof InterfaceType)) {
        // Otherwise, must be a Union type, which does not define fields.
        return [];
    }

    /** @var FieldsTrait $type */
    $possibleFieldNames = array_keys($type->getFields());
    return suggestionList($fieldName, $possibleFieldNames);
}
