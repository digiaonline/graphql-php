<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Error\GraphQLException;
use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Error\UndefinedException;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Type\Definition\AbstractTypeInterface;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\LeafTypeInterface;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\SerializableTypeInterface;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\UnionType;
use InvalidArgumentException;
use React\Promise\ExtendedPromiseInterface;
use React\Promise\FulfilledPromise;
use React\Promise\PromiseInterface;
use Throwable;
use function Digia\GraphQL\Type\SchemaMetaFieldDefinition;
use function Digia\GraphQL\Type\TypeMetaFieldDefinition;
use function Digia\GraphQL\Type\TypeNameMetaFieldDefinition;
use function Digia\GraphQL\Util\toString;
use function React\Promise\all as promiseAll;

class Executor
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
     * @var FieldCollector
     */
    protected $fieldCollector;

    /**
     * @var array
     */
    protected $finalResult;

    /**
     * @var array
     */
    private static $defaultFieldResolver = [__CLASS__, 'defaultFieldResolver'];

    /**
     * Executor constructor.
     * @param ExecutionContext $context
     * @param FieldCollector   $fieldCollector
     */
    public function __construct(ExecutionContext $context, FieldCollector $fieldCollector)
    {
        $this->context        = $context;
        $this->fieldCollector = $fieldCollector;
    }

    /**
     * @return array|null
     * @throws ExecutionException
     * @throws \Throwable
     */
    public function execute(): ?array
    {
        $schema    = $this->context->getSchema();
        $operation = $this->context->getOperation();
        $rootValue = $this->context->getRootValue();

        $objectType = $this->getOperationType($schema, $operation);

        $fields               = [];
        $visitedFragmentNames = [];
        $path                 = [];

        $fields = $this->fieldCollector->collectFields(
            $objectType,
            $operation->getSelectionSet(),
            $fields,
            $visitedFragmentNames
        );

        try {
            $result = $operation->getOperation() === 'mutation'
                ? $this->executeFieldsSerially($objectType, $rootValue, $path, $fields)
                : $this->executeFields($objectType, $rootValue, $path, $fields);
        } catch (\Throwable $ex) {
            $this->context->addError(new ExecutionException($ex->getMessage()));

            return [null];
        }

        return $result;
    }

    /**
     * @param Schema                  $schema
     * @param OperationDefinitionNode $operation
     * @return ObjectType|null
     * @throws ExecutionException
     */
    public function getOperationType(Schema $schema, OperationDefinitionNode $operation): ?ObjectType
    {
        switch ($operation->getOperation()) {
            case 'query':
                return $schema->getQueryType();
            case 'mutation':
                $mutationType = $schema->getMutationType();
                if (null === $mutationType) {
                    throw new ExecutionException(
                        'Schema is not configured for mutations',
                        [$operation]
                    );
                }
                return $mutationType;
            case 'subscription':
                $subscriptionType = $schema->getSubscriptionType();
                if (null === $subscriptionType) {
                    throw new ExecutionException(
                        'Schema is not configured for subscriptions',
                        [$operation]
                    );
                }
                return $subscriptionType;
            default:
                throw new ExecutionException(
                    'Can only execute queries, mutations and subscriptions',
                    [$operation]
                );
        }
    }

    /**
     * Implements the "Evaluating selection sets" section of the spec for "write" mode.
     *
     * @param ObjectType $objectType
     * @param mixed      $rootValue
     * @param array      $path
     * @param array      $fields
     * @return array
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public function executeFieldsSerially(
        ObjectType $objectType,
        $rootValue,
        array $path,
        array $fields
    ): array {
        $finalResults = [];

        $promise = new FulfilledPromise([]);

        $resolve = function ($results, $fieldName, $path, $objectType, $rootValue, $fieldNodes) {
            $fieldPath   = $path;
            $fieldPath[] = $fieldName;
            try {
                $result = $this->resolveField($objectType, $rootValue, $fieldNodes, $fieldPath);
            } catch (UndefinedException $ex) {
                return null;
            }

            if ($this->isPromise($result)) {
                /** @var ExtendedPromiseInterface $result */
                return $result->then(function ($resolvedResult) use ($fieldName, $results) {
                    $results[$fieldName] = $resolvedResult;
                    return $results;
                });
            }

            $results[$fieldName] = $result;

            return $results;
        };

        foreach ($fields as $fieldName => $fieldNodes) {
            $promise = $promise->then(function ($resolvedResults) use (
                $resolve,
                $fieldName,
                $path,
                $objectType,
                $rootValue,
                $fieldNodes
            ) {
                return $resolve($resolvedResults, $fieldName, $path, $objectType, $rootValue, $fieldNodes);
            });
        }

        $promise->then(function ($resolvedResults) use (&$finalResults) {
            $finalResults = $resolvedResults ?? [];
        })->otherwise(function ($ex) {
            $this->context->addError($ex);
        });

        return $finalResults;
    }

    /**
     * @param Schema     $schema
     * @param ObjectType $parentType
     * @param string     $fieldName
     * @return Field|null
     */
    public function getFieldDefinition(
        Schema $schema,
        ObjectType $parentType,
        string $fieldName
    ): ?Field {
        $schemaMetaFieldDefinition   = SchemaMetaFieldDefinition();
        $typeMetaFieldDefinition     = TypeMetaFieldDefinition();
        $typeNameMetaFieldDefinition = TypeNameMetaFieldDefinition();

        if ($fieldName === $schemaMetaFieldDefinition->getName() && $schema->getQueryType() === $parentType) {
            return $schemaMetaFieldDefinition;
        }

        if ($fieldName === $typeMetaFieldDefinition->getName() && $schema->getQueryType() === $parentType) {
            return $typeMetaFieldDefinition;
        }

        if ($fieldName === $typeNameMetaFieldDefinition->getName()) {
            return $typeNameMetaFieldDefinition;
        }

        $fields = $parentType->getFields();

        return $fields[$fieldName] ?? null;
    }

    /**
     * @param TypeInterface $fieldType
     * @param FieldNode[]   $fieldNodes
     * @param ResolveInfo   $info
     * @param array         $path
     * @param mixed         $result
     * @return array|mixed|null
     * @throws \Throwable
     */
    public function completeValueCatchingError(
        TypeInterface $fieldType,
        array $fieldNodes,
        ResolveInfo $info,
        array $path,
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

            if ($this->isPromise($completed)) {
                $context = $this->context;
                /** @var ExtendedPromiseInterface $completed */
                return $completed->then(null, function ($error) use ($context, $fieldNodes, $path) {
                    //@TODO Handle $error better
                    if ($error instanceof \Exception) {
                        $context->addError($this->buildLocatedError($error, $fieldNodes, $path));
                    } else {
                        $context->addError(
                            $this->buildLocatedError(
                                new ExecutionException($error ?? 'An unknown error occurred.'),
                                $fieldNodes,
                                $path
                            )
                        );
                    }
                    return new FulfilledPromise(null);
                });
            }

            return $completed;
        } catch (\Throwable $ex) {
            $this->context->addError($this->buildLocatedError($ex, $fieldNodes, $path));
            return null;
        }
    }


    /**
     * @param TypeInterface $fieldType
     * @param FieldNode[]   $fieldNodes
     * @param ResolveInfo   $info
     * @param array         $path
     * @param mixed         $result
     * @return array|mixed
     * @throws \Throwable
     */
    public function completeValueWithLocatedError(
        TypeInterface $fieldType,
        array $fieldNodes,
        ResolveInfo $info,
        array $path,
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
        } catch (\Throwable $ex) {
            throw $this->buildLocatedError($ex, $fieldNodes, $path);
        }
    }

    /**
     * Implements the "Evaluating selection sets" section of the spec for "read" mode.
     *
     * @param ObjectType $objectType
     * @param mixed      $rootValue
     * @param array      $path
     * @param array      $fields
     * @return array
     * @throws \Throwable
     */
    protected function executeFields(
        ObjectType $objectType,
        $rootValue,
        array $path,
        array $fields
    ): array {
        $finalResults       = [];
        $doesContainPromise = false;

        foreach ($fields as $fieldName => $fieldNodes) {
            $fieldPath   = $path;
            $fieldPath[] = $fieldName;

            try {
                $result = $this->resolveField($objectType, $rootValue, $fieldNodes, $fieldPath);
            } catch (UndefinedException $ex) {
                continue;
            }

            $doesContainPromise = $doesContainPromise || $this->isPromise($result);

            $finalResults[$fieldName] = $result;
        }

        if ($doesContainPromise) {
            $keys    = \array_keys($finalResults);
            $promise = promiseAll(\array_values($finalResults));

            $promise->then(function ($values) use ($keys, &$finalResults) {
                /** @noinspection ForeachSourceInspection */
                foreach ($values as $i => $value) {
                    $finalResults[$keys[$i]] = $value;
                }
            });
        }

        return $finalResults;
    }

    /**
     * @param ObjectType  $parentType
     * @param mixed       $rootValue
     * @param FieldNode[] $fieldNodes
     * @param array       $path
     * @return array|mixed|null
     * @throws UndefinedException
     * @throws Throwable
     */
    protected function resolveField(
        ObjectType $parentType,
        $rootValue,
        array $fieldNodes,
        array $path
    ) {
        /** @var FieldNode $fieldNode */
        $fieldNode = $fieldNodes[0];

        $field = $this->getFieldDefinition($this->context->getSchema(), $parentType, $fieldNode->getNameValue());

        if (null === $field) {
            throw new UndefinedException('Undefined field definition.');
        }

        $info = $this->createResolveInfo($fieldNodes, $fieldNode, $field, $parentType, $path, $this->context);

        $resolveCallback = $this->determineResolveCallback($field, $parentType);

        $result = $this->resolveFieldValueOrError(
            $field,
            $fieldNode,
            $resolveCallback,
            $rootValue,
            $this->context,
            $info
        );

        $result = $this->completeValueCatchingError(
            $field->getType(),
            $fieldNodes,
            $info,
            $path,
            $result
        );

        return $result;
    }

    /**
     * @param Field      $field
     * @param ObjectType $objectType
     * @return callable|mixed|null
     */
    protected function determineResolveCallback(Field $field, ObjectType $objectType)
    {
        if ($field->hasResolveCallback()) {
            return $field->getResolveCallback();
        }

        if ($objectType->hasResolveCallback()) {
            return $objectType->getResolveCallback();
        }

        return $this->context->getFieldResolver() ?? self::$defaultFieldResolver;
    }

    /**
     * @param TypeInterface $returnType
     * @param FieldNode[]   $fieldNodes
     * @param ResolveInfo   $info
     * @param array         $path
     * @param mixed         $result
     * @return array|mixed
     * @throws InvariantException
     * @throws InvalidTypeException
     * @throws ExecutionException
     * @throws \Throwable
     */
    protected function completeValue(
        TypeInterface $returnType,
        array $fieldNodes,
        ResolveInfo $info,
        array $path,
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
                    \sprintf(
                        'Cannot return null for non-nullable field %s.%s.',
                        $info->getParentType(),
                        $info->getFieldName()
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

        // TODO: Make a function for checking abstract type?
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
     * @param FieldNode[]           $fieldNodes
     * @param ResolveInfo           $info
     * @param array                 $path
     * @param mixed                 $result
     * @return array|PromiseInterface
     * @throws ExecutionException
     * @throws InvalidTypeException
     * @throws InvariantException
     * @throws \Throwable
     */
    protected function completeAbstractValue(
        AbstractTypeInterface $returnType,
        array $fieldNodes,
        ResolveInfo $info,
        array $path,
        &$result
    ) {
        $runtimeType = $returnType->resolveType($result, $this->context->getContextValue(), $info);

        if (null === $runtimeType) {
            // TODO: Display warning
            $runtimeType = $this->defaultTypeResolver($result, $this->context->getContextValue(), $info, $returnType);
        }

        if ($this->isPromise($runtimeType)) {
            /** @var ExtendedPromiseInterface $runtimeType */
            return $runtimeType->then(function ($resolvedRuntimeType) use (
                $returnType,
                $fieldNodes,
                $info,
                $path,
                &$result
            ) {
                return $this->completeObjectValue(
                    $this->ensureValidRuntimeType($resolvedRuntimeType, $returnType, $info, $result),
                    $fieldNodes,
                    $info,
                    $path,
                    $result
                );
            });
        }

        return $this->completeObjectValue(
            $this->ensureValidRuntimeType($runtimeType, $returnType, $info, $result),
            $fieldNodes,
            $info,
            $path,
            $result
        );
    }

    /**
     * @param NamedTypeInterface|string $runtimeTypeOrName
     * @param NamedTypeInterface        $returnType
     * @param ResolveInfo               $info
     * @param mixed                     $result
     * @return TypeInterface|ObjectType|null
     * @throws ExecutionException
     * @throws InvariantException
     */
    protected function ensureValidRuntimeType(
        $runtimeTypeOrName,
        NamedTypeInterface $returnType,
        ResolveInfo $info,
        &$result
    ) {
        /** @var NamedTypeInterface $runtimeType */
        $runtimeType = \is_string($runtimeTypeOrName)
            ? $this->context->getSchema()->getType($runtimeTypeOrName)
            : $runtimeTypeOrName;

        $runtimeTypeName = $runtimeType->getName();
        $returnTypeName  = $returnType->getName();

        if (!$runtimeType instanceof ObjectType) {
            $parentTypeName = $info->getParentType()->getName();
            $fieldName      = $info->getFieldName();

            throw new ExecutionException(
                \sprintf(
                    'Abstract type %s must resolve to an Object type at runtime for field %s.%s ' .
                    'with value "%s", received "%s".',
                    $returnTypeName,
                    $parentTypeName,
                    $fieldName,
                    $result,
                    $runtimeTypeName
                )
            );
        }

        if (!$this->context->getSchema()->isPossibleType($returnType, $runtimeType)) {
            throw new ExecutionException(
                \sprintf('Runtime Object type "%s" is not a possible type for "%s".', $runtimeTypeName, $returnTypeName)
            );
        }

        if ($runtimeType !== $this->context->getSchema()->getType($runtimeType->getName())) {
            throw new ExecutionException(
                \sprintf(
                    'Schema must contain unique named types but contains multiple types named "%s". ' .
                    'Make sure that `resolveType` function of abstract type "%s" returns the same ' .
                    'type instance as referenced anywhere else within the schema.',
                    $runtimeTypeName,
                    $returnTypeName
                )
            );
        }

        return $runtimeType;
    }

    /**
     * @param mixed                 $value
     * @param mixed                 $context
     * @param ResolveInfo           $info
     * @param AbstractTypeInterface $abstractType
     * @return NamedTypeInterface|mixed|null
     * @throws InvariantException
     */
    protected function defaultTypeResolver(
        $value,
        $context,
        ResolveInfo $info,
        AbstractTypeInterface $abstractType
    ) {
        /** @var ObjectType[] $possibleTypes */
        $possibleTypes           = $info->getSchema()->getPossibleTypes($abstractType);
        $promisedIsTypeOfResults = [];

        if (\is_array($value) && isset($value['__typename'])) {
            return $value['__typename'];
        }

        foreach ($possibleTypes as $index => $type) {
            $isTypeOfResult = $type->isTypeOf($value, $context, $info);

            if ($this->isPromise($isTypeOfResult)) {
                $promisedIsTypeOfResults[$index] = $isTypeOfResult;
                continue;
            }

            if ($isTypeOfResult === true) {
                return $type;
            }

            if (\is_array($value)) {
                // TODO: Make `type` configurable
                /** @noinspection NestedPositiveIfStatementsInspection */
                if (isset($value['type']) && $value['type'] === $type->getName()) {
                    return $type;
                }
            }
        }

        if (!empty($promisedIsTypeOfResults)) {
            return promiseAll($promisedIsTypeOfResults)
                ->then(function ($isTypeOfResults) use ($possibleTypes) {
                    /** @noinspection ForeachSourceInspection */
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
     * @param FieldNode[] $fieldNodes
     * @param ResolveInfo $info
     * @param array       $path
     * @param mixed       $result
     * @return array|\React\Promise\Promise
     * @throws \Throwable
     */
    protected function completeListValue(
        ListType $returnType,
        array $fieldNodes,
        ResolveInfo $info,
        array $path,
        &$result
    ) {
        $itemType = $returnType->getOfType();

        $completedItems     = [];
        $doesContainPromise = false;

        if (!\is_array($result) && !($result instanceof \Traversable)) {
            /** @noinspection ThrowRawExceptionInspection */
            throw new \Exception(
                \sprintf(
                    'Expected Array or Traversable, but did not find one for field %s.%s.',
                    $info->getParentType()->getName(),
                    $info->getFieldName()
                )
            );
        }

        foreach ($result as $key => $item) {
            $fieldPath          = $path;
            $fieldPath[]        = $key;
            $completedItem      = $this->completeValueCatchingError($itemType, $fieldNodes, $info, $fieldPath, $item);
            $completedItems[]   = $completedItem;
            $doesContainPromise = $doesContainPromise || $this->isPromise($completedItem);
        }

        return $doesContainPromise
            ? promiseAll($completedItems)
            : $completedItems;
    }

    /**
     * @param LeafTypeInterface|SerializableTypeInterface $returnType
     * @param mixed                                       $result
     * @return mixed
     * @throws ExecutionException
     */
    protected function completeLeafValue($returnType, &$result)
    {
        $serializedResult = $returnType->serialize($result);

        if ($serializedResult === null) {
            // TODO: Make a method for this type of exception
            throw new ExecutionException(
                \sprintf('Expected value of type "%s" but received: %s.', (string)$returnType, toString($result))
            );
        }

        return $serializedResult;
    }

    /**
     * @param ObjectType  $returnType
     * @param array       $fieldNodes
     * @param ResolveInfo $info
     * @param array       $path
     * @param mixed       $result
     * @return array
     * @throws ExecutionException
     * @throws InvalidTypeException
     * @throws InvariantException
     * @throws \Throwable
     */
    protected function completeObjectValue(
        ObjectType $returnType,
        array $fieldNodes,
        ResolveInfo $info,
        array $path,
        &$result
    ): array {
        if (null !== $returnType->getIsTypeOf()) {
            $isTypeOf = $returnType->isTypeOf($result, $this->context->getContextValue(), $info);

            // TODO: Check for promise?
            if (!$isTypeOf) {
                throw new ExecutionException(
                    sprintf('Expected value of type "%s" but received: %s.', (string)$returnType, toString($result))
                );
            }
        }

        return $this->executeSubFields($returnType, $fieldNodes, $path, $result);
    }

    /**
     * @param Field            $field
     * @param FieldNode        $fieldNode
     * @param callable         $resolveCallback
     * @param mixed            $rootValue
     * @param ExecutionContext $context
     * @param ResolveInfo      $info
     * @return array|\Throwable
     */
    protected function resolveFieldValueOrError(
        Field $field,
        FieldNode $fieldNode,
        ?callable $resolveCallback,
        $rootValue,
        ExecutionContext $context,
        ResolveInfo $info
    ) {
        try {
            $result = $resolveCallback(
                $rootValue,
                coerceArgumentValues($field, $fieldNode, $context->getVariableValues()),
                $context->getContextValue(),
                $info
            );
        } catch (\Throwable $error) {
            return $error;
        }

        return $result;
    }

    /**
     * @param ObjectType  $returnType
     * @param FieldNode[] $fieldNodes
     * @param array       $path
     * @param mixed       $result
     * @return array
     * @throws ExecutionException
     * @throws InvalidTypeException
     * @throws InvariantException
     * @throws Throwable
     */
    protected function executeSubFields(
        ObjectType $returnType,
        array $fieldNodes,
        array $path,
        &$result
    ): array {
        $subFields            = [];
        $visitedFragmentNames = [];

        foreach ($fieldNodes as $fieldNode) {
            if (null !== $fieldNode->getSelectionSet()) {
                $subFields = $this->fieldCollector->collectFields(
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
     * @param mixed $value
     * @return bool
     */
    protected function isPromise($value): bool
    {
        return $value instanceof ExtendedPromiseInterface;
    }

    /**
     * @param \Throwable $originalException
     * @param array      $nodes
     * @param array      $path
     * @return ExecutionException
     */
    protected function buildLocatedError(
        \Throwable $originalException,
        array $nodes = [],
        array $path = []
    ): ExecutionException {
        return new ExecutionException(
            $originalException->getMessage(),
            $originalException instanceof GraphQLException
                ? $originalException->getNodes()
                : $nodes,
            $originalException instanceof GraphQLException
                ? $originalException->getSource()
                : null,
            $originalException instanceof GraphQLException
                ? $originalException->getPositions()
                : null,
            $originalException instanceof GraphQLException
                ? ($originalException->getPath() ?? $path)
                : $path,
            $originalException
        );
    }

    /**
     * @param FieldNode[]      $fieldNodes
     * @param FieldNode        $fieldNode
     * @param Field            $field
     * @param ObjectType       $parentType
     * @param array|null       $path
     * @param ExecutionContext $context
     * @return ResolveInfo
     */
    protected function createResolveInfo(
        array $fieldNodes,
        FieldNode $fieldNode,
        Field $field,
        ObjectType $parentType,
        ?array $path,
        ExecutionContext $context
    ): ResolveInfo {
        return new ResolveInfo(
            $fieldNode->getNameValue(),
            $fieldNodes,
            $field->getType(),
            $parentType,
            $path,
            $context->getSchema(),
            $context->getFragments(),
            $context->getRootValue(),
            $context->getOperation(),
            $context->getVariableValues()
        );
    }

    /**
     * Try to resolve a field without any field resolver function.
     *
     * @param array|object $rootValue
     * @param array        $arguments
     * @param mixed        $contextValues
     * @param ResolveInfo  $info
     * @return mixed|null
     */
    public static function defaultFieldResolver($rootValue, array $arguments, $contextValues, ResolveInfo $info)
    {
        $fieldName = $info->getFieldName();
        $property  = null;

        if (\is_array($rootValue) && isset($rootValue[$fieldName])) {
            $property = $rootValue[$fieldName];
        }

        if (\is_object($rootValue)) {
            $getter = 'get' . \ucfirst($fieldName);
            if (\method_exists($rootValue, $getter)) {
                $property = $rootValue->{$getter}();
            } elseif (\method_exists($rootValue, $fieldName)) {
                $property = $rootValue->{$fieldName}($rootValue, $arguments, $contextValues, $info);
            } elseif (\property_exists($rootValue, $fieldName)) {
                $property = $rootValue->{$fieldName};
            }
        }

        return $property instanceof \Closure
            ? $property($rootValue, $arguments, $contextValues, $info)
            : $property;
    }
}
