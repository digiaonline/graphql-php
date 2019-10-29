<?php

namespace Digia\GraphQL\Language\Visitor;

use Digia\GraphQL\Util\ConversionException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\DirectiveNode;
use Digia\GraphQL\Language\Node\EnumValueNode;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\InlineFragmentNode;
use Digia\GraphQL\Language\Node\ListValueNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\ObjectFieldNode;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Node\SelectionSetNode;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use Digia\GraphQL\Type\Definition\Argument;
use GraphQL\Contracts\TypeSystem\Type\CompositeTypeInterface;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Util\TypeASTConverter;
use Digia\GraphQL\Util\TypeInfo;
use function Digia\GraphQL\Type\getNamedType;
use function Digia\GraphQL\Type\getNullableType;
use function Digia\GraphQL\Type\isInputType;
use function Digia\GraphQL\Type\isOutputType;
use function Digia\GraphQL\Util\find;

class TypeInfoVisitor implements VisitorInterface
{
    /**
     * @var TypeInfo
     */
    protected $typeInfo;

    /**
     * @var VisitorInterface
     */
    protected $visitor;

    /**
     * TypeInfoVisitor constructor.
     * @param TypeInfo         $typeInfo
     * @param VisitorInterface $visitor
     */
    public function __construct(TypeInfo $typeInfo, VisitorInterface $visitor)
    {
        $this->typeInfo = $typeInfo;
        $this->visitor  = $visitor;
    }

    /**
     * @inheritdoc
     *
     * @throws ConversionException
     * @throws InvariantException
     */
    public function enterNode(NodeInterface $node): VisitorResult
    {
        $schema = $this->typeInfo->getSchema();

        if ($node instanceof SelectionSetNode) {
            $namedType = getNamedType($this->typeInfo->getType());
            $this->typeInfo->pushParentType($namedType instanceof CompositeTypeInterface ? $namedType : null);
        } elseif ($node instanceof FieldNode) {
            $parentType      = $this->typeInfo->getParentType();
            $fieldDefinition = null;
            $fieldType       = null;
            if (null !== $parentType) {
                $fieldDefinition = $this->typeInfo->resolveFieldDefinition($schema, $parentType, $node);
                if (null !== $fieldDefinition) {
                    $fieldType = $fieldDefinition->getType();
                }
            }
            $this->typeInfo->pushFieldDefinition($fieldDefinition);
            $this->typeInfo->pushType(isOutputType($fieldType) ? $fieldType : null);
        } elseif ($node instanceof DirectiveNode) {
            $this->typeInfo->setDirective($schema->getDirective($node->getNameValue()));
        } elseif ($node instanceof OperationDefinitionNode) {
            $type      = null;
            $operation = $node->getOperation();
            if ($operation === 'query') {
                $type = $schema->getQueryType();
            } elseif ($operation === 'mutation') {
                $type = $schema->getMutationType();
            } elseif ($operation === 'subscription') {
                $type = $schema->getSubscriptionType();
            }
            $this->typeInfo->pushType($type instanceof ObjectType ? $type : null);
        } elseif ($node instanceof InlineFragmentNode || $node instanceof FragmentDefinitionNode) {
            $typeCondition = $node->getTypeCondition();
            $outputType    = null !== $typeCondition
                ? TypeASTConverter::convert($schema, $typeCondition)
                : getNamedType($this->typeInfo->getType());
            $this->typeInfo->pushType(isOutputType($outputType) ? $outputType : null);
        } elseif ($node instanceof VariableDefinitionNode) {
            $inputType = TypeASTConverter::convert($schema, $node->getType());
            /** @noinspection PhpParamsInspection */
            $this->typeInfo->pushInputType(isInputType($inputType) ? $inputType : null);
        } elseif ($node instanceof ArgumentNode) {
            $argumentType = null;
            /** @var Argument|null $argumentDefinition */
            $argumentDefinition = null;
            $fieldOrDirective   = $this->typeInfo->getDirective() ?: $this->typeInfo->getFieldDefinition();
            if (null !== $fieldOrDirective) {
                $argumentDefinition = find(
                    $fieldOrDirective->getArguments(),
                    function (Argument $argument) use ($node) {
                        return $argument->getName() === $node->getNameValue();
                    }
                );
                if (null !== $argumentDefinition) {
                    $argumentType = $argumentDefinition->getType();
                }
            }
            $this->typeInfo->setArgument($argumentDefinition);
            $this->typeInfo->pushDefaultValue(null !== $argumentDefinition
                ? $argumentDefinition->getDefaultValue()
                : null);
            $this->typeInfo->pushInputType(isInputType($argumentType) ? $argumentType : null);
        } elseif ($node instanceof ListValueNode) {
            $listType = getNullableType($this->typeInfo->getInputType());
            $itemType = $listType instanceof ListType ? $listType->getOfType() : $listType;
            // List positions never have a default value.
            $this->typeInfo->pushDefaultValue(null);
            $this->typeInfo->pushInputType(isInputType($itemType) ? $itemType : null);
        } elseif ($node instanceof ObjectFieldNode) {
            $objectType     = getNamedType($this->typeInfo->getInputType());
            $inputField     = null;
            $inputFieldType = null;
            if ($objectType instanceof InputObjectType) {
                $fields     = $objectType->getFields();
                $inputField = $fields[$node->getNameValue()] ?? null;
                if (null !== $inputField) {
                    $inputFieldType = $inputField->getType();
                }
            }
            $this->typeInfo->pushDefaultValue(null !== $inputField ? $inputField->getDefaultValue() : null);
            /** @noinspection PhpParamsInspection */
            $this->typeInfo->pushInputType(isInputType($inputFieldType) ? $inputFieldType : null);
        } elseif ($node instanceof EnumValueNode) {
            $enumType  = getNamedType($this->typeInfo->getInputType());
            $enumValue = null;
            if ($enumType instanceof EnumType) {
                $enumValue = $enumType->getValue($node->getValue());
            }
            $this->typeInfo->setEnumValue($enumValue);
        }

        return $this->visitor->enterNode($node);
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(NodeInterface $node): VisitorResult
    {
        $VisitorResult = $this->visitor->leaveNode($node);
        $newNode       = $VisitorResult->getValue();

        if ($newNode instanceof SelectionSetNode) {
            $this->typeInfo->popParentType();
        } elseif ($newNode instanceof FieldNode) {
            $this->typeInfo->popFieldDefinition();
            $this->typeInfo->popType();
        } elseif ($newNode instanceof DirectiveNode) {
            $this->typeInfo->setDirective(null);
        } elseif ($newNode instanceof OperationDefinitionNode) {
            $this->typeInfo->popType();
        } elseif ($newNode instanceof InlineFragmentNode || $newNode instanceof FragmentDefinitionNode) {
            $this->typeInfo->popType();
        } elseif ($newNode instanceof VariableDefinitionNode) {
            $this->typeInfo->popInputType();
        } elseif ($newNode instanceof ArgumentNode) {
            $this->typeInfo->setArgument(null);
            $this->typeInfo->popDefaultValue();
            $this->typeInfo->popInputType();
        } elseif ($newNode instanceof ListValueNode) {
            $this->typeInfo->popInputType();
        } elseif ($newNode instanceof ObjectFieldNode) {
            $this->typeInfo->popDefaultValue();
            $this->typeInfo->popInputType();
        } elseif ($newNode instanceof EnumValueNode) {
            $this->typeInfo->setEnumValue(null);
        }

        return $VisitorResult;
    }
}
