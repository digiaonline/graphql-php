<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\UndefinedException;
use Digia\GraphQL\Execution\Resolver\ResolveInfo;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\InlineFragmentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Node\SelectionSetNode;
use Digia\GraphQL\Type\Definition\AbstractTypeInterface;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\LeafTypeInterface;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\UnionType;
use Digia\GraphQL\Type\Schema;
use React\Promise\ExtendedPromiseInterface;
use React\Promise\PromiseInterface;
use function Digia\GraphQL\Type\SchemaMetaFieldDefinition;
use function Digia\GraphQL\Type\TypeMetaFieldDefinition;
use function Digia\GraphQL\Type\TypeNameMetaFieldDefinition;
use function Digia\GraphQL\Util\toString;
use function Digia\GraphQL\Util\typeFromAST;

/**
 * Class AbstractStrategy
 * @package Digia\GraphQL\Execution\Strategies
 */
abstract class ExecutionStrategy
{
    /**
     * @var ExecutionContext
     */
    protected $context;

    /**
     * @var OperationDefinitionNode
     */
    protected $operation;

    /**
     * @var mixed
     */
    protected $rootValue;


    /**
     * @var ValuesResolver
     */
    protected $valuesResolver;

    /**
     * @var array
     */
    protected $finalResult;

    /**
     * @var array
     */
    protected static $defaultFieldResolver = [__CLASS__, 'defaultFieldResolver'];

    /**
     * AbstractStrategy constructor.
     * @param ExecutionContext        $context
     *
     * @param OperationDefinitionNode $operation
     */
    public function __construct(
        ExecutionContext $context,
        OperationDefinitionNode $operation,
        $rootValue
    ) {
        $this->context        = $context;
        $this->operation      = $operation;
        $this->rootValue      = $rootValue;
        $this->valuesResolver = new ValuesResolver();
    }

    /**
     * @return array|null
     */
    abstract function execute(): ?array;

    /**
     * @param ObjectType       $runtimeType
     * @param SelectionSetNode $selectionSet
     * @param                  $fields
     * @param                  $visitedFragmentNames
     * @return mixed
     * @throws InvalidTypeException
     * @throws \Digia\GraphQL\Error\ExecutionException
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    protected function collectFields(
        ObjectType $runtimeType,
        SelectionSetNode $selectionSet,
        &$fields,
        &$visitedFragmentNames
    ) {
        foreach ($selectionSet->getSelections() as $selection) {
            // Check if this Node should be included first
            if (!$this->shouldIncludeNode($selection)) {
                continue;
            }
            // Collect fields
            if ($selection instanceof FieldNode) {
                $fieldName = $this->getFieldNameKey($selection);

                if (!isset($fields[$fieldName])) {
                    $fields[$fieldName] = [];
                }

                $fields[$fieldName][] = $selection;
            } elseif ($selection instanceof InlineFragmentNode) {
                if (!$this->doesFragmentConditionMatch($selection, $runtimeType)) {
                    continue;
                }

                $this->collectFields($runtimeType, $selection->getSelectionSet(), $fields, $visitedFragmentNames);
            } elseif ($selection instanceof FragmentSpreadNode) {
                $fragmentName = $selection->getNameValue();

                if (!empty($visitedFragmentNames[$fragmentName])) {
                    continue;
                }

                $visitedFragmentNames[$fragmentName] = true;
                /** @var FragmentDefinitionNode $fragment */
                $fragment = $this->context->getFragments()[$fragmentName];
                $this->collectFields($runtimeType, $fragment->getSelectionSet(), $fields, $visitedFragmentNames);
            }
        }

        return $fields;
    }


    /**
     * @param $node
     * @return bool
     * @throws InvalidTypeException
     * @throws \Digia\GraphQL\Error\ExecutionException
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    private function shouldIncludeNode(NodeInterface $node): bool
    {

        $contextVariables = $this->context->getVariableValues();

        $skip = $this->valuesResolver->getDirectiveValues(GraphQLSkipDirective(), $node, $contextVariables);

        if ($skip && $skip['if'] === true) {
            return false;
        }

        $include = $this->valuesResolver->getDirectiveValues(GraphQLIncludeDirective(), $node, $contextVariables);

        if ($include && $include['if'] === false) {
            return false;
        }

        return true;
    }

    /**
     * @param FragmentDefinitionNode|InlineFragmentNode $fragment
     * @param ObjectType                                $type
     * @return bool
     * @throws InvalidTypeException
     */
    private function doesFragmentConditionMatch(
        NodeInterface $fragment,
        ObjectType $type
    ): bool {
        $typeConditionNode = $fragment->getTypeCondition();

        if (!$typeConditionNode) {
            return true;
        }

        $conditionalType = typeFromAST($this->context->getSchema(), $typeConditionNode);

        if ($conditionalType === $type) {
            return true;
        }

        if ($conditionalType instanceof AbstractTypeInterface) {
            return $this->context->getSchema()->isPossibleType($conditionalType, $type);
        }

        return false;
    }

    /**
     * @TODO: consider to move this to FieldNode
     * @param FieldNode $node
     * @return string
     */
    private function getFieldNameKey(FieldNode $node): string
    {
        return $node->getAlias() ? $node->getAlias()->getValue() : $node->getNameValue();
    }

    /**
     * Implements the "Evaluating selection sets" section of the spec for "read" mode.
     * @param ObjectType $objectType
     * @param            $rootValue
     * @param            $path
     * @param            $fields
     * @return array
     * @throws InvalidTypeException
     * @throws \Digia\GraphQL\Error\ExecutionException
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Throwable
     */
    protected function executeFields(
        ObjectType $objectType,
        $rootValue,
        $path,
        $fields
    ): array {
        $finalResults      = [];
        $isContainsPromise = false;

        foreach ($fields as $fieldName => $fieldNodes) {
            $fieldPath   = $path;
            $fieldPath[] = $fieldName;

            try {
                $result = $this->resolveField($objectType, $rootValue, $fieldNodes, $fieldPath);
            } catch (UndefinedException $ex) {
                continue;
            }

            if (!$isContainsPromise && $this->isPromise($result)) {
                $isContainsPromise = true;
            }

            $finalResults[$fieldName] = $result;
        }

        if ($isContainsPromise) {
            $keys    = array_keys($finalResults);
            $promise = \React\Promise\all(array_values($finalResults));
            $promise->then(function ($values) use ($keys, &$finalResults) {
                foreach ($values as $i => $value) {
                    $finalResults[$keys[$i]] = $value;
                }
            });
        }

        return $finalResults;
    }

    /**
     * Implements the "Evaluating selection sets" section of the spec for "write" mode.
     *
     * @param ObjectType $objectType
     * @param            $rootValue
     * @param            $path
     * @param            $fields
     * @return array
     * @throws InvalidTypeException
     * @throws \Digia\GraphQL\Error\ExecutionException
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Throwable
     */
    public function executeFieldsSerially(
        ObjectType $objectType,
        $rootValue,
        $path,
        $fields
    ) {
        //@TODO execute fields serially
        $finalResults = [];

        foreach ($fields as $fieldName => $fieldNodes) {
            $fieldPath   = $path;
            $fieldPath[] = $fieldName;

            try {
                $result = $this->resolveField($objectType, $rootValue, $fieldNodes, $fieldPath);
            } catch (UndefinedException $ex) {
                continue;
            }

            $finalResults[$fieldName] = $result;
        }

        return $finalResults;
    }

    /**
     * @param Schema     $schema
     * @param ObjectType $parentType
     * @param string     $fieldName
     * @return \Digia\GraphQL\Type\Definition\Field|null
     * @throws InvalidTypeException
     */
    public function getFieldDefinition(
        Schema $schema,
        ObjectType $parentType,
        string $fieldName
    ) {
        $schemaMetaFieldDefinition   = SchemaMetaFieldDefinition();
        $typeMetaFieldDefinition     = TypeMetaFieldDefinition();
        $typeNameMetaFieldDefinition = TypeNameMetaFieldDefinition();

        if ($fieldName === $schemaMetaFieldDefinition->getName() && $schema->getQuery() === $parentType) {
            return $schemaMetaFieldDefinition;
        }

        if ($fieldName === $typeMetaFieldDefinition->getName() && $schema->getQuery() === $parentType) {
            return $typeMetaFieldDefinition;
        }

        if ($fieldName === $typeNameMetaFieldDefinition->getName()) {
            return $typeNameMetaFieldDefinition;
        }

        $fields = $parentType->getFields();

        return $fields[$fieldName] ?? null;
    }


    /**
     * @param ObjectType $parentType
     * @param            $rootValue
     * @param            $fieldNodes
     * @param            $path
     * @return array|null|\Throwable
     * @throws InvalidTypeException
     * @throws \Digia\GraphQL\Error\ExecutionException
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Throwable
     */
    protected function resolveField(
        ObjectType $parentType,
        $rootValue,
        $fieldNodes,
        $path
    ) {
        /** @var FieldNode $fieldNode */
        $fieldNode = $fieldNodes[0];

        $field = $this->getFieldDefinition($this->context->getSchema(), $parentType, $fieldNode->getNameValue());

        if (null === $field) {
            throw new UndefinedException('Undefined field definition.');
        }

        $info = $this->buildResolveInfo($fieldNodes, $fieldNode, $field, $parentType, $path, $this->context);

        $resolveFunction = $this->determineResolveFunction($field, $parentType, $this->context);

        $result = $this->resolveFieldValueOrError(
            $field,
            $fieldNode,
            $resolveFunction,
            $rootValue,
            $this->context,
            $info
        );

        $result = $this->completeValueCatchingError(
            $field->getType(),
            $fieldNodes,
            $info,
            $path,
            $result// $result is passed as $source
        );

        return $result;
    }

    /**
     * @param array            $fieldNodes
     * @param FieldNode        $fieldNode
     * @param Field            $field
     * @param ObjectType       $parentType
     * @param                  $path
     * @param ExecutionContext $context
     * @return ResolveInfo
     */
    private function buildResolveInfo(
        array $fieldNodes,
        FieldNode $fieldNode,
        Field $field,
        ObjectType $parentType,
        $path,
        ExecutionContext $context
    ) {
        return new ResolveInfo([
            'fieldName'      => $fieldNode->getNameValue(),
            'fieldNodes'     => $fieldNodes,
            'returnType'     => $field->getType(),
            'parentType'     => $parentType,
            'path'           => $path,
            'schema'         => $context->getSchema(),
            'fragments'      => $context->getFragments(),
            'rootValue'      => $context->getRootValue(),
            'operation'      => $context->getOperation(),
            'variableValues' => $context->getVariableValues(),
        ]);
    }

    /**
     * @param Field            $field
     * @param ObjectType       $objectType
     * @param ExecutionContext $context
     * @return callable|mixed|null
     */
    private function determineResolveFunction(
        Field $field,
        ObjectType $objectType,
        ExecutionContext $context
    ) {

        if ($field->hasResolve()) {
            return $field->getResolve();
        }

        if ($objectType->hasResolve()) {
            return $objectType->getResolve();
        }

        return $this->context->getFieldResolver() ?? self::$defaultFieldResolver;
    }

    /**
     * @param TypeInterface $fieldType
     * @param               $fieldNodes
     * @param ResolveInfo   $info
     * @param               $path
     * @param               $result
     * @return null
     * @throws \Throwable
     */
    public function completeValueCatchingError(
        TypeInterface $fieldType,
        $fieldNodes,
        ResolveInfo $info,
        $path,
        &$result
    ) {
        if ($fieldType instanceof NonNullType) {
            return $this->completeValueWithLocatedError(
                $fieldType,
                $fieldNodes,
                $info,
                $path,
                $result
            );
        }

        try {
            $completed = $this->completeValueWithLocatedError(
                $fieldType,
                $fieldNodes,
                $info,
                $path,
                $result
            );

            return $completed;
        } catch (\Exception $ex) {
            $this->context->addError(new ExecutionException($ex->getMessage()));
            return null;
        }
    }

    /**
     * @param TypeInterface $fieldType
     * @param               $fieldNodes
     * @param ResolveInfo   $info
     * @param               $path
     * @param               $result
     * @throws \Throwable
     */
    public function completeValueWithLocatedError(
        TypeInterface $fieldType,
        $fieldNodes,
        ResolveInfo $info,
        $path,
        $result
    ) {
        try {
            $completed = $this->completeValue(
                $fieldType,
                $fieldNodes,
                $info,
                $path,
                $result
            );
            return $completed;
        } catch (\Exception $ex) {
            //@TODO throw located error
            throw $ex;
        } catch (\Throwable $ex) {
            //@TODO throw located error
            throw $ex;
        }
    }

    /**
     * @param TypeInterface $returnType
     * @param               $fieldNodes
     * @param ResolveInfo   $info
     * @param               $path
     * @param               $result
     * @return array|mixed
     * @throws ExecutionException
     * @throws \Throwable
     */
    private function completeValue(
        TypeInterface $returnType,
        $fieldNodes,
        ResolveInfo $info,
        $path,
        &$result
    ) {
        if ($this->isPromise($result)) {
            /** @var ExtendedPromiseInterface $result */
            return $result->then(function (&$value) use ($returnType, $fieldNodes, $info, $path) {
                return $this->completeValue($returnType, $fieldNodes, $info, $path, $value);
            });
        }

        if ($result instanceof \Throwable) {
            throw $result;
        }

        // If result is null-like, return null.
        if (null === $result) {
            return null;
        }

        if ($returnType instanceof NonNullType) {
            $completed = $this->completeValue(
                $returnType->getOfType(),
                $fieldNodes,
                $info,
                $path,
                $result
            );

            if ($completed === null) {
                throw new ExecutionException(
                    sprintf(
                        'Cannot return null for non-nullable field %s.%s.',
                        $info->getParentType(), $info->getFieldName()
                    )
                );
            }

            return $completed;
        }

        // If field type is List, complete each item in the list with the inner type
        if ($returnType instanceof ListType) {
            return $this->completeListValue($returnType, $fieldNodes, $info, $path, $result);
        }


        // If field type is Scalar or Enum, serialize to a valid value, returning
        // null if serialization is not possible.
        if ($returnType instanceof LeafTypeInterface) {
            return $this->completeLeafValue($returnType, $result);
        }

        //@TODO Make a function for checking abstract type?
        if ($returnType instanceof InterfaceType || $returnType instanceof UnionType) {
            return $this->completeAbstractValue($returnType, $fieldNodes, $info, $path, $result);
        }

        // Field type must be Object, Interface or Union and expect sub-selections.
        if ($returnType instanceof ObjectType) {
            return $this->completeObjectValue($returnType, $fieldNodes, $info, $path, $result);
        }

        throw new ExecutionException("Cannot complete value of unexpected type \"{$returnType}\".");
    }

    /**
     * @param AbstractTypeInterface $returnType
     * @param                       $fieldNodes
     * @param ResolveInfo           $info
     * @param                       $path
     * @param                       $result
     * @return array|PromiseInterface
     * @throws ExecutionException
     * @throws InvalidTypeException
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Throwable
     */
    private function completeAbstractValue(
        AbstractTypeInterface $returnType,
        $fieldNodes,
        ResolveInfo $info,
        $path,
        &$result
    ) {
        $runtimeType = $returnType->resolveType($result, $this->context->getContextValue(), $info);

        if (null === $runtimeType) {
            //@TODO Show warning
            $runtimeType = $this->defaultTypeResolver($result, $this->context->getContextValue(), $info, $returnType);
        }

        if ($this->isPromise($runtimeType)) {
            /** @var PromiseInterface $runtimeType */
            return $runtimeType->then(function ($resolvedRuntimeType) use (
                $returnType,
                $fieldNodes,
                $info,
                $path,
                &
                $result
            ) {
                return $this->completeObjectValue(
                    $this->ensureValidRuntimeType(
                        $resolvedRuntimeType,
                        $returnType,
                        $fieldNodes,
                        $info,
                        $result
                    ),
                    $fieldNodes,
                    $info,
                    $path,
                    $result
                );
            });
        }

        return $this->completeObjectValue(
            $this->ensureValidRuntimeType(
                $runtimeType,
                $returnType,
                $fieldNodes,
                $info,
                $result
            ),
            $fieldNodes,
            $info,
            $path,
            $result
        );
    }

    /**
     * @param                       $runtimeTypeOrName
     * @param AbstractTypeInterface $returnType
     * @param                       $fieldNodes
     * @param ResolveInfo           $info
     * @param                       $result
     * @return TypeInterface|ObjectType|null
     * @throws ExecutionException
     */
    private function ensureValidRuntimeType(
        $runtimeTypeOrName,
        AbstractTypeInterface $returnType,
        $fieldNodes,
        ResolveInfo $info,
        &$result
    ) {
        $runtimeType = is_string($runtimeTypeOrName)
            ? $this->context->getSchema()->getType($runtimeTypeOrName)
            : $runtimeTypeOrName;

        $runtimeTypeName = is_string($runtimeType) ?: $runtimeType->getName();
        $returnTypeName  = $returnType->getName();

        if (!$runtimeType instanceof ObjectType) {
            $parentTypeName = $info->getParentType()->getName();
            $fieldName      = $info->getFieldName();

            throw new ExecutionException(
                "Abstract type {$returnTypeName} must resolve to an Object type at runtime " .
                "for field {$parentTypeName}.{$fieldName} with " .
                'value "' . $result . '", received "{$runtimeTypeName}".'
            );
        }

        if (!$this->context->getSchema()->isPossibleType($returnType, $runtimeType)) {
            throw new ExecutionException(
                "Runtime Object type \"{$runtimeTypeName}\" is not a possible type for \"{$returnTypeName}\"."
            );
        }

        if ($runtimeType !== $this->context->getSchema()->getType($runtimeType->getName())) {
            throw new ExecutionException(
                "Schema must contain unique named types but contains multiple types named \"{$runtimeTypeName}\". " .
                "Make sure that `resolveType` function of abstract type \"{$returnTypeName}\" returns the same " .
                "type instance as referenced anywhere else within the schema."
            );
        }

        return $runtimeType;
    }

    /**
     * @param                       $value
     * @param                       $context
     * @param ResolveInfo           $info
     * @param AbstractTypeInterface $abstractType
     * @return TypeInterface|null
     */
    private function defaultTypeResolver($value, $context, ResolveInfo $info, AbstractTypeInterface $abstractType)
    {
        $possibleTypes           = $info->getSchema()->getPossibleTypes($abstractType);
        $promisedIsTypeOfResults = [];
        $type                    = null;

        foreach ($possibleTypes as $index => $type) {
            $isTypeOfResult = $type->isTypeOf($value, $context, $info);

            if (null !== $isTypeOfResult) {
                if ($this->isPromise($isTypeOfResult)) {
                    $promisedIsTypeOfResults[$index] = $isTypeOfResult;
                } elseif ($isTypeOfResult) {
                    return $type;
                }
            }
        }

        if (!empty($promisedIsTypeOfResults)) {
            return \React\Promise\all($promisedIsTypeOfResults)
                ->then(function ($isTypeOfResults) use ($possibleTypes) {
                    foreach ($isTypeOfResults as $index => $result) {
                        if ($result) {
                            return $possibleTypes[$index];
                        }
                    }
                    return null;
                });
        }

        return null;
    }

    /**
     * @param ListType    $returnType
     * @param             $fieldNodes
     * @param ResolveInfo $info
     * @param             $path
     * @param             $result
     * @return array|\React\Promise\Promise
     * @throws \Throwable
     */
    private function completeListValue(
        ListType $returnType,
        $fieldNodes,
        ResolveInfo $info,
        $path,
        &$result
    ) {
        $itemType = $returnType->getOfType();

        $completedItems  = [];
        $containsPromise = false;
        foreach ($result as $key => $item) {
            $fieldPath        = $path;
            $fieldPath[]      = $key;
            $completedItem    = $this->completeValueCatchingError($itemType, $fieldNodes, $info, $fieldPath, $item);
            $completedItems[] = $completedItem;
            if (!$containsPromise && $this->isPromise($completedItem)) {
                $containsPromise = true;
            }
        }

        return $containsPromise ? \React\Promise\all($completedItems) : $completedItems;
    }

    /**
     * @param LeafTypeInterface $returnType
     * @param                   $result
     * @return mixed
     * @throws ExecutionException
     */
    private function completeLeafValue(LeafTypeInterface $returnType, &$result)
    {
        $serializedResult = $returnType->serialize($result);

        if ($serializedResult === null) {
            throw new ExecutionException(
                sprintf('Expected a value of type "%s" but received: %s', toString($returnType), toString($result))
            );
        }

        return $serializedResult;
    }

    /**
     * @param ObjectType  $returnType
     * @param             $fieldNodes
     * @param ResolveInfo $info
     * @param             $path
     * @param             $result
     * @return array
     * @throws ExecutionException
     * @throws InvalidTypeException
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Throwable
     */
    private function completeObjectValue(
        ObjectType $returnType,
        $fieldNodes,
        ResolveInfo $info,
        $path,
        &$result
    ) {
        return $this->collectAndExecuteSubFields(
            $returnType,
            $fieldNodes,
            $info,
            $path,
            $result
        );
    }

    /**
     * @param Field            $field
     * @param FieldNode        $fieldNode
     * @param callable         $resolveFunction
     * @param                  $rootValue
     * @param ExecutionContext $context
     * @param ResolveInfo      $info
     * @return array|\Throwable
     */
    private function resolveFieldValueOrError(
        Field $field,
        FieldNode $fieldNode,
        ?callable $resolveFunction,
        $rootValue,
        ExecutionContext $context,
        ResolveInfo $info
    ) {
        try {
            $args = $this->valuesResolver->coerceArgumentValues($field, $fieldNode, $context->getVariableValues());

            return $resolveFunction($rootValue, $args, $context->getContextValue(), $info);
        } catch (\Throwable $error) {
            return $error;
        }
    }

    /**
     * Try to resolve a field without any field resolver function.
     *
     * @param array|object $rootValue
     * @param              $args
     * @param              $context
     * @param ResolveInfo  $info
     * @return mixed|null
     */
    public static function defaultFieldResolver($rootValue, $args, $context, ResolveInfo $info)
    {
        $fieldName = $info->getFieldName();
        $property  = null;

        if (is_array($rootValue) && isset($rootValue[$fieldName])) {
            $property = $rootValue[$fieldName];
        }

        if (is_object($rootValue)) {
            $getter = 'get' . ucfirst($fieldName);
            if (method_exists($rootValue, $getter)) {
                $property = $rootValue->{$getter}();
            } elseif (property_exists($rootValue, $fieldName)) {
                $property = $rootValue->{$fieldName};
            }
        }


        return $property instanceof \Closure ? $property($rootValue, $args, $context, $info) : $property;
    }

    /**
     * @param ObjectType  $returnType
     * @param             $fieldNodes
     * @param ResolveInfo $info
     * @param             $path
     * @param             $result
     * @return array
     * @throws InvalidTypeException
     * @throws \Digia\GraphQL\Error\ExecutionException
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Throwable
     */
    private function collectAndExecuteSubFields(
        ObjectType $returnType,
        $fieldNodes,
        ResolveInfo $info,
        $path,
        &$result
    ) {
        $subFields            = [];
        $visitedFragmentNames = [];

        foreach ($fieldNodes as $fieldNode) {
            /** @var FieldNode $fieldNode */
            if ($fieldNode->getSelectionSet() !== null) {
                $subFields = $this->collectFields(
                    $returnType,
                    $fieldNode->getSelectionSet(),
                    $subFields,
                    $visitedFragmentNames
                );
            }
        }

        if (!empty($subFields)) {
            return $this->executeFields($returnType, $result, $path, $subFields);
        }

        return $result;
    }

    /**
     * @param $value
     * @return bool
     */
    protected function isPromise($value): bool
    {
        return $value instanceof ExtendedPromiseInterface;
    }
}
