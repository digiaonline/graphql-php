<?php

namespace Digia\GraphQL\Execution\Strategy;

use Digia\GraphQL\Error\GraphQLException;
use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Execution\ExecutionContext;
use Digia\GraphQL\Execution\ExecutionException;
use Digia\GraphQL\Execution\InvalidReturnTypeException;
use Digia\GraphQL\Execution\ResolveInfo;
use Digia\GraphQL\Execution\UndefinedFieldException;
use Digia\GraphQL\Execution\ValuesResolver;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Type\Definition\AbstractTypeInterface;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\LeafTypeInterface;
use Digia\GraphQL\Type\Definition\ListType;
use GraphQL\Contracts\TypeSystem\Type\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\SerializableTypeInterface;
use GraphQL\Contracts\TypeSystem\Type\TypeInterface;
use Digia\GraphQL\Util\ConversionException;
use React\Promise\PromiseInterface;
use function Digia\GraphQL\Type\SchemaMetaFieldDefinition;
use function Digia\GraphQL\Type\TypeMetaFieldDefinition;
use function Digia\GraphQL\Type\TypeNameMetaFieldDefinition;

abstract class AbstractExecutionStrategy implements ExecutionStrategyInterface
{
    /**
     * @var ExecutionContext
     */
    protected $context;

    /**
     * @var FieldCollector
     */
    protected $fieldCollector;

    /**
     * @var callable
     */
    protected $typeResolverCallback;

    /**
     * @var callable
     */
    protected $fieldResolverCallback;

    /**
     * @param ObjectType $parentType
     * @param mixed      $rootValue
     * @param array      $path
     * @param array      $fields
     * @return array|PromiseInterface
     * @throws \Throwable
     * @throws ExecutionException
     */
    abstract public function executeFields(ObjectType $parentType, $rootValue, array $path, array $fields);

    /**
     * AbstractExecutionStrategy constructor.
     *
     * @param ExecutionContext $context
     * @param FieldCollector   $fieldCollector
     * @param callable|null    $typeResolverCallback
     * @param callable|null    $fieldResolverCallback
     */
    public function __construct(
        ExecutionContext $context,
        FieldCollector $fieldCollector,
        ?callable $typeResolverCallback = null,
        ?callable $fieldResolverCallback = null
    ) {
        $this->context               = $context;
        $this->fieldCollector        = $fieldCollector;
        $this->typeResolverCallback  = $typeResolverCallback ?? [$this, 'defaultTypeResolver'];
        $this->fieldResolverCallback = $fieldResolverCallback ?? [$this, 'defaultFieldResolver'];
    }

    /**
     * @inheritdoc
     * @throws ExecutionException
     * @throws InvalidTypeException
     * @throws InvariantException
     * @throws ConversionException
     * @throws \Throwable
     */
    public function execute()
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

        // Errors from sub-fields of a NonNull type may propagate to the top level,
        // at which point we still log the error and null the parent field, which
        // in this case is the entire response.
        try {
            $result = $this->executeFields($objectType, $rootValue, $path, $fields);
        } catch (ExecutionException $exception) {
            $this->context->addError($exception);
            return null;
        } catch (\Throwable $exception) {
            $exception = !$exception instanceof ExecutionException
                ? $this->normalizeException($exception, $fields, $path)
                : $exception;

            $this->context->addError($exception);

            return null;
        }

        return $result;
    }

    /**
     * @param Schema                  $schema
     * @param OperationDefinitionNode $operation
     *
     * @return ObjectType|null
     * @throws ExecutionException
     */
    protected function getOperationType(Schema $schema, OperationDefinitionNode $operation): ?ObjectType
    {
        switch ($operation->getOperation()) {
            case 'query':
                return $schema->getQueryType();
            case 'mutation':
                $mutationType = $schema->getMutationType();

                if (null === $mutationType) {
                    throw new ExecutionException('Schema is not configured for mutations.', [$operation]);
                }

                return $mutationType;
            case 'subscription':
                $subscriptionType = $schema->getSubscriptionType();

                if (null === $subscriptionType) {
                    throw new ExecutionException('Schema is not configured for subscriptions.', [$operation]);
                }

                return $subscriptionType;
            default:
                throw new ExecutionException('Can only execute queries, mutations and subscriptions.', [$operation]);
        }
    }

    /**
     * Resolves the field on the given source object. In particular, this
     * figures out the value that the field returns by calling its resolve function,
     * then calls completeValue to complete promises, serialize scalars, or execute
     * the sub-selection-set for objects.
     *
     * @param ObjectType  $parentType
     * @param mixed       $rootValue
     * @param FieldNode[] $fieldNodes
     * @param string[]    $path
     *
     * @return mixed
     * @throws \Throwable
     * @throws UndefinedFieldException
     */
    protected function resolveField(ObjectType $parentType, $rootValue, array $fieldNodes, array $path)
    {
        $fieldNode = $fieldNodes[0];

        $fieldName = $fieldNode->getNameValue();
        $field     = $this->getFieldDefinition($this->context->getSchema(), $parentType, $fieldName);

        if (null === $field) {
            throw new UndefinedFieldException($fieldName);
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
     * @param Field            $field
     * @param FieldNode        $fieldNode
     * @param callable         $resolveCallback
     * @param mixed            $rootValue
     * @param ExecutionContext $context
     * @param ResolveInfo      $info
     *
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
            // Build an associative array of arguments from the field.arguments AST, using the
            // variables scope to fulfill any variable references.
            $result = $resolveCallback(
                $rootValue,
                ValuesResolver::coerceArgumentValues($field, $fieldNode, $context->getVariableValues()),
                $context->getContextValue(),
                $info
            );

            if ($result instanceof PromiseInterface) {
                return $result->then(null, function ($exception) use ($fieldNode, $info) {
                    return !$exception instanceof ExecutionException
                        ? $this->normalizeException($exception, [$fieldNode], $info->getPath())
                        : $exception;
                });
            }

            return $result;
        } catch (\Throwable $exception) {
            return $exception;
        }
    }

    /**
     * Normalizes exceptions which are usually a \Throwable,
     * but can even be a string or null when resolving promises.
     *
     * @param mixed           $exception
     * @param NodeInterface[] $nodes
     * @param array           $path
     * @return ExecutionException
     */
    protected function normalizeException($exception, array $nodes, array $path = []): ExecutionException
    {
        if ($exception instanceof \Throwable) {
            return new ExecutionException(
                $exception->getMessage(),
                $nodes,
                null,
                null,
                $path,
                null,
                $exception
            );
        }

        if (\is_string($exception)) {
            return new ExecutionException(
                $exception,
                $nodes,
                null,
                null,
                $path
            );
        }

        return new ExecutionException(
            '',
            $nodes,
            null,
            null,
            $path
        );
    }

    /**
     * This is a small wrapper around completeValue which detects and logs error in the execution context.
     *
     * @param TypeInterface $returnType
     * @param FieldNode[]   $fieldNodes
     * @param ResolveInfo   $info
     * @param array         $path
     * @param mixed         $result
     *
     * @return array|null|PromiseInterface
     * @throws \Throwable
     */
    public function completeValueCatchingError(
        TypeInterface $returnType,
        array $fieldNodes,
        ResolveInfo $info,
        array $path,
        &$result
    ) {
        try {
            $completed = $result instanceof PromiseInterface
                ? $result->then(function ($resolvedResult) use ($returnType, $fieldNodes, $info, $path) {
                    return $this->completeValue($returnType, $fieldNodes, $info, $path, $resolvedResult);
                })
                : $this->completeValue($returnType, $fieldNodes, $info, $path, $result);

            if ($completed instanceof PromiseInterface) {
                // Note: we don't rely on a `catch` method, but we do expect "thenable"
                // to take a second callback for the error case.
                return $completed->then(null, function ($exception) use ($fieldNodes, $path, $returnType) {
                    $this->handleFieldError($exception, $fieldNodes, $path, $returnType);
                });
            }

            return $completed;
        } catch (\Throwable $exception) {
            $this->handleFieldError($exception, $fieldNodes, $path, $returnType);
            return null;
        }
    }

    /**
     * Implements the instructions for completeValue as defined in the
     * "Field entries" section of the spec.
     *
     * If the field type is Non-Null, then this recursively completes the value
     * for the inner type. It throws a field error if that completion returns null,
     * as per the "Nullability" section of the spec.
     *
     * If the field type is a List, then this recursively completes the value
     * for the inner type on each item in the list.
     *
     * If the field type is a Scalar or Enum, ensures the completed value is a legal
     * value of the type by calling the `serialize` method of GraphQL type
     * definition.
     *
     * If the field is an abstract type, determine the runtime type of the value
     * and then complete based on that type
     *
     * Otherwise, the field type expects a sub-selection set, and will complete the
     * value by evaluating all sub-selections.
     *
     * @param TypeInterface $returnType
     * @param FieldNode[]   $fieldNodes
     * @param ResolveInfo   $info
     * @param array         $path
     * @param mixed         $result
     *
     * @return array|null|PromiseInterface
     * @throws \Throwable
     */
    protected function completeValue(
        TypeInterface $returnType,
        array $fieldNodes,
        ResolveInfo $info,
        array $path,
        &$result
    ) {
        // If result is an Error, throw a located error.
        if ($result instanceof \Throwable) {
            throw $result;
        }

        // If field type is NonNull, complete for inner type, and throw field error if result is null.
        if ($returnType instanceof NonNullType) {
            $completed = $this->completeValue(
                $returnType->getOfType(),
                $fieldNodes,
                $info,
                $path,
                $result
            );

            if (null !== $completed) {
                return $completed;
            }

            throw new ExecutionException(
                \sprintf(
                    'Cannot return null for non-nullable field %s.%s.',
                    (string)$info->getParentType(),
                    $info->getFieldName()
                )
            );
        }

        // If result is null, return null.
        if (null === $result) {
            return null;
        }

        // If field type is a leaf type, Scalar or Enum, serialize to a valid value,
        // returning null if serialization is not possible.
        if ($returnType instanceof ListType) {
            return $this->completeListValue($returnType, $fieldNodes, $info, $path, $result);
        }

        // If field type is Scalar or Enum, serialize to a valid value, returning
        // null if serialization is not possible.
        if ($returnType instanceof LeafTypeInterface) {
            return $this->completeLeafValue($returnType, $result);
        }

        // If field type is an abstract type, Interface or Union, determine the
        // runtime Object type and complete for that type.
        if ($returnType instanceof AbstractTypeInterface) {
            return $this->completeAbstractValue($returnType, $fieldNodes, $info, $path, $result);
        }

        // If field type is Object, execute and complete all sub-selections.
        if ($returnType instanceof ObjectType) {
            return $this->completeObjectValue($returnType, $fieldNodes, $info, $path, $result);
        }

        throw new ExecutionException(\sprintf('Cannot complete value of unexpected type "%s".', (string)$returnType));
    }

    /**
     * @param ListType    $returnType
     * @param FieldNode[] $fieldNodes
     * @param ResolveInfo $info
     * @param array       $path
     * @param mixed       $result
     *
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
        if (!\is_array($result) && !$result instanceof \Traversable) {
            throw new InvariantException(
                \sprintf(
                    'Expected Array or Traversable, but did not find one for field %s.%s.',
                    (string)$info->getParentType(),
                    $info->getFieldName()
                )
            );
        }

        $itemType           = $returnType->getOfType();
        $completedItems     = [];
        $containsPromise = false;

        foreach ($result as $key => $item) {
            $fieldPath          = $path;
            $fieldPath[]        = $key;
            $completedItem      = $this->completeValueCatchingError($itemType, $fieldNodes, $info, $fieldPath, $item);
            $completedItems[]   = $completedItem;
            $containsPromise = $containsPromise || $completedItem instanceof PromiseInterface;
        }

        return $containsPromise
            ? \React\Promise\all($completedItems)
            : $completedItems;
    }

    /**
     * @param LeafTypeInterface|SerializableTypeInterface $returnType
     * @param mixed                                       $result
     *
     * @return array|PromiseInterface
     * @throws InvalidReturnTypeException
     */
    protected function completeLeafValue($returnType, &$result)
    {
        $result = $returnType->serialize($result);

        if (null === $result) {
            throw new InvalidReturnTypeException($returnType, $result);
        }

        return $result;
    }

    /**
     * @param AbstractTypeInterface $returnType
     * @param FieldNode[]           $fieldNodes
     * @param ResolveInfo           $info
     * @param string[]              $path
     * @param mixed                 $result
     *
     * @return array|PromiseInterface
     * @throws \Throwable
     */
    protected function completeAbstractValue(
        AbstractTypeInterface $returnType,
        array $fieldNodes,
        ResolveInfo $info,
        array $path,
        &$result
    ) {
        $runtimeType = $returnType->hasResolveTypeCallback()
            ? $returnType->resolveType($result, $this->context->getContextValue(), $info)
            : \call_user_func(
                $this->typeResolverCallback,
                $result,
                $this->context->getContextValue(),
                $info,
                $returnType
            );

        if ($runtimeType instanceof PromiseInterface) {
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

        $returnType = $this->ensureValidRuntimeType($runtimeType, $returnType, $info, $result);

        return $this->completeObjectValue(
            $returnType,
            $fieldNodes,
            $info,
            $path,
            $result
        );
    }

    /**
     * @param ObjectType  $returnType
     * @param array       $fieldNodes
     * @param ResolveInfo $info
     * @param array       $path
     * @param mixed       $result
     *
     * @return array|PromiseInterface
     * @throws ExecutionException
     * @throws InvalidReturnTypeException
     * @throws \Throwable
     */
    protected function completeObjectValue(
        ObjectType $returnType,
        array $fieldNodes,
        ResolveInfo $info,
        array $path,
        &$result
    ) {
        // If there is an isTypeOfCallback predicate function, call it with the
        // current result. If isTypeOfCallback returns false, then raise an error rather
        // than continuing execution.
        if ($returnType->hasIsTypeOfCallback()) {
            $isTypeOf = $returnType->isTypeOf($result, $this->context->getContextValue(), $info);

            if ($isTypeOf instanceof PromiseInterface) {
                return $isTypeOf->then(function ($resolvedIsTypeOf) use ($returnType, $result, $fieldNodes, $path) {
                    if (true === $resolvedIsTypeOf) {
                        return $this->executeSubFields($returnType, $fieldNodes, $path, $result);
                    }

                    throw new InvalidReturnTypeException($returnType, $result, $fieldNodes);
                });
            }

            if (false === $isTypeOf) {
                throw new InvalidReturnTypeException($returnType, $result, $fieldNodes);
            }
        }

        return $this->executeSubFields($returnType, $fieldNodes, $path, $result);
    }

    /**
     * @param ObjectType  $returnType
     * @param FieldNode[] $fieldNodes
     * @param array       $path
     * @param mixed       $result
     *
     * @return array|PromiseInterface
     * @throws \Throwable
     */
    protected function executeSubFields(ObjectType $returnType, array $fieldNodes, array $path, &$result)
    {
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
     * @param NamedTypeInterface|string $runtimeTypeOrName
     * @param NamedTypeInterface        $returnType
     * @param ResolveInfo               $info
     * @param mixed                     $result
     *
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
     * @param Schema     $schema
     * @param ObjectType $parentType
     * @param string     $fieldName
     *
     * @return Field|null
     * @throws InvariantException
     */
    public function getFieldDefinition(Schema $schema, ObjectType $parentType, string $fieldName): ?Field
    {
        if ($fieldName === '__schema' && $schema->getQueryType() === $parentType) {
            return SchemaMetaFieldDefinition();
        }

        if ($fieldName === '__type' && $schema->getQueryType() === $parentType) {
            return TypeMetaFieldDefinition();
        }

        if ($fieldName === '__typename') {
            return TypeNameMetaFieldDefinition();
        }

        $fields = $parentType->getFields();

        return $fields[$fieldName] ?? null;
    }

    /**
     * @param Field      $field
     * @param ObjectType $parentType
     *
     * @return callable
     */
    protected function determineResolveCallback(Field $field, ObjectType $parentType): callable
    {
        if ($field->hasResolveCallback()) {
            return $field->getResolveCallback();
        }

        if ($parentType->hasResolveCallback()) {
            return $parentType->getResolveCallback();
        }

        if ($this->context->hasFieldResolver()) {
            return $this->context->getFieldResolver();
        }

        return $this->fieldResolverCallback;
    }

    /**
     * @param \Throwable    $originalException
     * @param array         $fieldNodes
     * @param array         $path
     * @param TypeInterface $returnType
     * @throws ExecutionException
     */
    protected function handleFieldError(
        \Throwable $originalException,
        array $fieldNodes,
        array $path,
        TypeInterface $returnType
    ): void {
        $exception = $this->buildLocatedError($originalException, $fieldNodes, $path);

        // If the field type is non-nullable, then it is resolved without any
        // protection from errors, however it still properly locates the error.
        if ($returnType instanceof NonNullType) {
            throw $exception;
        }

        // Otherwise, error protection is applied, logging the error and resolving
        // a null value for this field if one is encountered.
        $this->context->addError($exception);
    }

    /**
     * @param \Throwable      $originalException
     * @param NodeInterface[] $nodes
     * @param string[]        $path
     *
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
            null,
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
     *
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
     * @param mixed                 $value
     * @param mixed                 $context
     * @param ResolveInfo           $info
     * @param AbstractTypeInterface $abstractType
     *
     * @return mixed
     * @throws InvariantException
     */
    public static function defaultTypeResolver(
        $value,
        $context,
        ResolveInfo $info,
        AbstractTypeInterface $abstractType
    ) {
        // First, look for `__typename`.
        if (\is_array($value) && isset($value['__typename'])) {
            return $value['__typename'];
        }

        // Otherwise, test each possible type.

        /** @var ObjectType[] $possibleTypes */
        $possibleTypes = $info->getSchema()->getPossibleTypes($abstractType);
        $promises      = [];

        foreach ($possibleTypes as $index => $type) {
            $isTypeOf = $type->isTypeOf($value, $context, $info);

            if ($isTypeOf instanceof PromiseInterface) {
                $promises[$index] = $isTypeOf;
            }

            if (true === $isTypeOf) {
                return $type;
            }
        }

        if (!empty($promises)) {
            return \React\Promise\all($promises)->then(function ($resolvedPromises) use ($possibleTypes) {
                foreach ($resolvedPromises as $index => $result) {
                    if (true === $result) {
                        return $possibleTypes[$index];
                    }
                }

                return null;
            });
        }

        return null;
    }

    /**
     * Try to resolve a field without any field resolver function.
     *
     * @param array|object $rootValue
     * @param array        $arguments
     * @param mixed        $contextValues
     * @param ResolveInfo  $info
     *
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
