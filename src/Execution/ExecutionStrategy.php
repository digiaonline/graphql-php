<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Execution\Resolver\ResolveInfo;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\InlineFragmentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Node\SelectionSetNode;
use Digia\GraphQL\Type\Definition\AbstractTypeInterface;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Schema;
use function Digia\GraphQL\Type\SchemaMetaFieldDefinition;
use function Digia\GraphQL\Type\TypeMetaFieldDefinition;
use function Digia\GraphQL\Type\TypeNameMetaFieldDefinition;
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
     * AbstractStrategy constructor.
     * @param ExecutionContext        $context
     *
     * @param OperationDefinitionNode $operation
     */
    public function __construct(
        ExecutionContext $context,
        OperationDefinitionNode $operation,
        $rootValue,
        $valueResolver
    ) {
        $this->context        = $context;
        $this->operation      = $operation;
        $this->rootValue      = $rootValue;
        $this->valuesResolver = $valueResolver;
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
        $fields,
        $visitedFragmentNames
    ) {
        foreach ($selectionSet->getSelections() as $selection) {
            switch ($selection->getKind()) {
                case NodeKindEnum::FIELD:
                    /** @var FieldNode $selection */
                    $fieldName = $this->getFieldNameKey($selection);
                    if (!isset($runtimeType->getFields()[$selection->getNameValue()])) {
                        continue 2;
                    }
                    if (!isset($fields[$fieldName])) {
                        $fields[$fieldName] = new \ArrayObject();
                    }
                    $fields[$fieldName][] = $selection;
                    break;
                case NodeKindEnum::INLINE_FRAGMENT:
                    /** @var FragmentDefinitionNode $selection */
                    if (!$this->shouldIncludeNode($selection) ||
                        !$this->doesFragmentConditionMatch($selection, $runtimeType)
                    ) {
                        continue 2;
                    }
                    $this->collectFields(
                        $runtimeType,
                        $selection->getSelectionSet(),
                        $fields,
                        $visitedFragmentNames
                    );
                    break;
                case NodeKindEnum::FRAGMENT_SPREAD:
                    $fragmentName = $selection->getNameValue();
                    if (!empty($visitedFragmentNames[$fragmentName]) || !$this->shouldIncludeNode($selection)) {
                        continue 2;
                    }
                    $visitedFragmentNames[$fragmentName] = true;
                    /** @var FragmentDefinitionNode $fragment */
                    $fragment = $this->context->getFragments()[$fragmentName];
                    $this->collectFields(
                        $runtimeType,
                        $fragment->getSelectionSet(),
                        $fields,
                        $visitedFragmentNames
                    );
                    break;
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
    private function shouldIncludeNode(NodeInterface $node)
    {
        $skip = $this->valuesResolver->getDirectiveValues(GraphQLSkipDirective(), $node,
            $this->context->getVariableValues());

        if ($skip && $skip['if'] === true) {
            return false;
        }

        $include = $this->valuesResolver->getDirectiveValues(GraphQLSkipDirective(), $node,
            $this->context->getVariableValues());

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
    private function doesFragmentConditionMatch(NodeInterface $fragment, ObjectType $type)
    {
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
    private function getFieldNameKey(FieldNode $node)
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
     */
    protected function executeFields(
        ObjectType $objectType,
        $rootValue,
        $path,
        $fields
    ): array {
        $finalResults = [];

        foreach ($fields as $fieldName => $fieldNodes) {
            $fieldPath   = $path;
            $fieldPath[] = $fieldName;

            $result = $this->resolveField($objectType,
                $rootValue,
                $fieldNodes,
                $fieldPath
            );

            $finalResults[$fieldName] = $result;
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
     */
    public function executeFieldsSerially(ObjectType $objectType, $rootValue, $path, $fields)
    {
        $finalResults = [];

        foreach ($fields as $fieldName => $fieldNodes) {
            $fieldPath   = $path;
            $fieldPath[] = $fieldName;

            $result = $this->resolveField($objectType,
                $rootValue,
                $fieldNodes,
                $fieldPath
            );

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
    public function getFieldDefinition(Schema $schema, ObjectType $parentType, string $fieldName)
    {
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

        if (!$field) {
            return null;
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

        $returnType = ($field->getType() instanceof ObjectType) ? $field->getType() : $parentType;

        $result = $this->collectAndExecuteSubFields(
            $returnType,
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
        \ArrayAccess $fieldNodes,
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
     * @param ObjectType       $parentType
     * @param ExecutionContext $context
     * @return callable|mixed|null
     */
    private function determineResolveFunction(Field $field, ObjectType $parentType, ExecutionContext $context)
    {
        if ($field->hasResolve()) {
            return $field->getResolve();
        }

        if ($parentType->hasResolve()) {
            return $parentType->getResolve();
        }

        return $this->context->getFieldResolver();
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
        callable $resolveFunction,
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
     * @param ObjectType  $returnType
     * @param             $fieldNodes
     * @param ResolveInfo $info
     * @param             $path
     * @param             $result
     * @return array
     * @throws InvalidTypeException
     * @throws \Digia\GraphQL\Error\ExecutionException
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    private function collectAndExecuteSubFields(
        ObjectType $returnType,
        $fieldNodes,
        ResolveInfo $info,
        $path,
        &$result
    ) {
        $subFields = new \ArrayObject();

        foreach ($fieldNodes as $fieldNode) {
            /** @var FieldNode $fieldNode */
            if ($fieldNode->getSelectionSet() !== null) {
                $subFields = $this->collectFields(
                    $returnType,
                    $fieldNode->getSelectionSet(),
                    $subFields,
                    new \ArrayObject()
                );
            }
        }

        if ($subFields->count()) {
            return $this->executeFields($returnType, $result, $path, $subFields);
        }

        return $result;
    }
}
