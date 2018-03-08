<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Execution\Resolver\ResolveInfo;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\AST\Node\SelectionSetNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Schema;
use function Digia\GraphQL\Type\SchemaMetaFieldDefinition;
use function Digia\GraphQL\Type\TypeMetaFieldDefinition;
use function Digia\GraphQL\Type\TypeNameMetaFieldDefinition;

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
        $rootValue
    ) {
        $this->context   = $context;
        $this->operation = $operation;
        $this->rootValue = $rootValue;
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
     * @return \ArrayObject
     * @throws \Exception
     */
    protected function collectFields(
        ObjectType $runtimeType,
        SelectionSetNode $selectionSet,
        $fields,
        $visitedFragmentNames
    ) {
        foreach ($selectionSet->getSelections() as $selection) {
            /** @var FieldNode $selection */
            switch ($selection->getKind()) {
                case NodeKindEnum::FIELD:
                    $name = $this->getFieldNameKey($selection);
                    if (!isset($runtimeType->getFields()[$selection->getNameValue()])) {
                        continue 2;
                    }
                    if (!isset($fields[$name])) {
                        $fields[$name] = new \ArrayObject();
                    }
                    $fields[$name][] = $selection;
                    break;
                case NodeKindEnum::INLINE_FRAGMENT:
                    //TODO check if should include this node
                    $this->collectFields(
                        $runtimeType,
                        $selection->getSelectionSet(),
                        $fields,
                        $visitedFragmentNames
                    );
                    break;
                case NodeKindEnum::FRAGMENT_SPREAD:
                    //TODO check if should include this node
                    if (!empty($visitedFragmentNames[$selection->getNameValue()])) {
                        continue 2;
                    }
                    $visitedFragmentNames[$selection->getNameValue()] = true;
                    /** @var FragmentDefinitionNode $fragment */
                    $fragment = $this->context->getFragments()[$selection->getNameValue()];
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
     * @TODO: consider to move this to FieldNode
     * @param FieldNode $node
     * @return string
     */
    private function getFieldNameKey(FieldNode $node)
    {
        return $node->getAlias() ? $node->getAlias()->getValue() : $node->getNameValue();
    }

    /**
     * Implements the "Evaluating selection sets" section of the spec
     * for "read" mode.
     * @param ObjectType $parentType
     * @param            $source
     * @param            $path
     * @param            $fields
     *
     * @return array
     *
     * @throws GraphQLError|\Exception
     * @throws \TypeError
     */
    protected function executeFields(
        ObjectType $parentType,
        $source,
        $path,
        $fields
    ): array {
        $finalResults = [];

        foreach ($fields as $fieldName => $fieldNodes) {
            $fieldPath   = $path;
            $fieldPath[] = $fieldName;

            $result = $this->resolveField($parentType,
                $source,
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
     * @throws \Exception
     * @throws \TypeError
     */
    public function getFieldDefinition(Schema $schema, ObjectType $parentType, string $fieldName)
    {
        if ($fieldName === SchemaMetaFieldDefinition()->getName() && $schema->getQuery() === $parentType) {
            return SchemaMetaFieldDefinition();
        }

        if ($fieldName === TypeMetaFieldDefinition()->getName() && $schema->getQuery() === $parentType) {
            return TypeNameMetaFieldDefinition();
        }

        if ($fieldName === TypeNameMetaFieldDefinition()->getName()) {
            return TypeNameMetaFieldDefinition();
        }

        $fields = $parentType->getFields();

        return isset($fields[$fieldName]) ? $fields[$fieldName] : null;
    }


    /**
     * @param ObjectType $parentType
     * @param            $source
     * @param            $fieldNodes
     * @param            $path
     *
     * @return mixed
     *
     * @throws GraphQLError|\Exception
     * @throws \TypeError
     */
    protected function resolveField(
        ObjectType $parentType,
        $source,
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

        $result = $this->resolveOrError(
            $field,
            $fieldNode,
            $resolveFunction,
            $source,
            $this->context,
            $info
        );

        $result = $this->collectAndExecuteSubFields(
            $parentType,
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
    private function buildResolveInfo(\ArrayAccess $fieldNodes, FieldNode $fieldNode, Field $field, ObjectType $parentType, $path, ExecutionContext $context)
    {
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
     * @param                  $source
     * @param ExecutionContext $context
     * @param ResolveInfo      $info
     * @return array|\Exception|\Throwable
     */
    private function resolveOrError(
        Field $field,
        FieldNode $fieldNode,
        callable $resolveFunction,
        $source,
        ExecutionContext $context,
        ResolveInfo $info
    ) {
        try {
            $args = getArgumentValues($field, $fieldNode, $context->getVariableValues());

            return $resolveFunction($source, $args, $context, $info);
        } catch (\Exception $error) {
            return $error;
        } catch (\Throwable $error) {
            return $error;
        }
    }

    /**
     * @param ObjectType  $returnType
     * @param FieldNode[] $fieldNodes
     * @param ResolveInfo $info
     * @param array       $path
     * @return array|\stdClass
     * @throws GraphQLError
     * @throws \Exception
     * @throws \TypeError
     */
    private function collectAndExecuteSubFields(
        ObjectType $returnType,
        $fieldNodes,
        ResolveInfo $info,
        $path,
        &$result
    )
    {
        $subFields = new \ArrayObject();

        foreach ($fieldNodes as $fieldNode) {
            if ($fieldNode->getSelectionSet() !== null) {
                $subFields = $this->collectFields(
                    $returnType,
                    $fieldNode->getSelectionSet(),
                    $subFields,
                    new \ArrayObject()
                );
            }
        }

        if($subFields->count()) {
            return $this->executeFields($returnType, $result, $path, $subFields);
        }

        return $result;
    }
}
