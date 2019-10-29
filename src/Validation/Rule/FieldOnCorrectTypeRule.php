<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Type\Definition\AbstractTypeInterface;
use Digia\GraphQL\Type\Definition\FieldsAwareInterface;
use Digia\GraphQL\Type\Definition\InterfaceType;
use GraphQL\Contracts\TypeSystem\Type\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\ObjectType;
use GraphQL\Contracts\TypeSystem\Type\OutputTypeInterface;
use GraphQL\Contracts\TypeSystem\Type\TypeInterface;
use Digia\GraphQL\Validation\ValidationException;
use function Digia\GraphQL\Util\suggestionList;
use function Digia\GraphQL\Validation\undefinedFieldMessage;

/**
 * Fields on correct type
 *
 * A GraphQL document is only valid if all fields selected are defined by the
 * parent type, or are an allowed meta field such as __typename.
 */
class FieldOnCorrectTypeRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    protected function enterField(FieldNode $node): VisitorResult
    {
        $type = $this->context->getParentType();

        if ($type instanceof OutputTypeInterface) {
            $fieldDefinition = $this->context->getFieldDefinition();

            if (null === $fieldDefinition) {
                $schema              = $this->context->getSchema();
                $fieldName           = $node->getNameValue();
                $suggestedTypeNames  = $this->getSuggestedTypeNames($schema, $type, $fieldName);
                $suggestedFieldNames = \count($suggestedTypeNames) !== 0
                    ? []
                    : $this->getSuggestedFieldNames($type, $fieldName);

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

        return new VisitorResult($node);
    }

    /**
     * Go through all of the implementations of type, as well as the interfaces
     * that they implement. If any of those types include the provided field,
     * suggest them, sorted by how often the type is referenced,  starting
     * with Interfaces.
     *
     * @param Schema        $schema
     * @param TypeInterface $type
     * @param string        $fieldName
     * @return array
     * @throws InvariantException
     */
    protected function getSuggestedTypeNames(Schema $schema, TypeInterface $type, string $fieldName): array
    {
        if (!$type instanceof AbstractTypeInterface) {
            // Otherwise, must be an Object type, which does not have possible fields.
            return [];
        }

        $suggestedObjectTypes = [];
        $interfaceUsageCount  = [];

        foreach ($schema->getPossibleTypes($type) as $possibleType) {
            if (!$possibleType instanceof FieldsAwareInterface) {
                continue;
            }

            $typeFields = $possibleType->getFields();

            if (!isset($typeFields[$fieldName])) {
                break;
            }

            if (!$possibleType instanceof NamedTypeInterface) {
                continue;
            }

            $suggestedObjectTypes[] = $possibleType->getName();

            if (!$possibleType instanceof ObjectType) {
                continue;
            }

            foreach ($possibleType->getInterfaces() as $possibleInterface) {
                $interfaceFields = $possibleInterface->getFields();

                if (!isset($interfaceFields[$fieldName])) {
                    break;
                }

                $interfaceName                       = $possibleInterface->getName();
                $interfaceUsageCount[$interfaceName] = ($interfaceUsageCount[$interfaceName] ?? 0) + 1;
            }
        }

        $suggestedInterfaceTypes = \array_keys($interfaceUsageCount);

        \uasort($suggestedInterfaceTypes, function ($a, $b) use ($interfaceUsageCount) {
            return $interfaceUsageCount[$b] - $interfaceUsageCount[$a];
        });

        return \array_merge($suggestedInterfaceTypes, $suggestedObjectTypes);
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
    protected function getSuggestedFieldNames(OutputTypeInterface $type, string $fieldName): array
    {
        if (!($type instanceof ObjectType || $type instanceof InterfaceType)) {
            // Otherwise, must be a Union type, which does not define fields.
            return [];
        }

        $possibleFieldNames = \array_keys($type->getFields());

        return suggestionList($fieldName, $possibleFieldNames);
    }
}
