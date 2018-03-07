<?php

namespace Digia\GraphQL\Util;

use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Type\Definition\Argument;
use Digia\GraphQL\Type\Definition\CompositeTypeInterface;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\EnumValue;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\InputTypeInterface;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\OutputTypeInterface;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\SchemaInterface;
use function Digia\GraphQL\Type\SchemaMetaFieldDefinition;
use function Digia\GraphQL\Type\TypeMetaFieldDefinition;
use function Digia\GraphQL\Type\TypeNameMetaFieldDefinition;

class TypeInfo
{
    /**
     * @var SchemaInterface
     */
    protected $schema;

    /**
     * @var array|OutputTypeInterface[]
     */
    protected $typeStack = [];

    /**
     * @var array|CompositeTypeInterface[]
     */
    protected $parentTypeStack = [];

    /**
     * @var array|InputTypeInterface[]
     */
    protected $inputTypeStack = [];

    /**
     * @var array|Field[]
     */
    protected $fieldDefinitionStack = [];

    /**
     * @var Directive
     */
    protected $directive;

    /**
     * @var Argument
     */
    protected $argument;

    /**
     * @var EnumValue
     */
    protected $enumValue;

    /**
     * @var callable|null
     */
    protected $getFieldDefinitionFunction;

    /**
     * TypeInfo constructor.
     * @param SchemaInterface $schema
     * @param callable|null   $getFieldDefinitionFunction
     */
    public function __construct(
        SchemaInterface $schema,
        ?callable $getFieldDefinitionFunction = null,
        ?TypeInterface $initialType = null
    ) {
        $this->schema = $schema;
        $this->getFieldDefinitionFunction = null !== $getFieldDefinitionFunction
            ? $getFieldDefinitionFunction
            : function (SchemaInterface $schema, TypeInterface $parentType, FieldNode $fieldNode) {
                return getFieldDefinition($schema, $parentType, $fieldNode);
            };

        if ($initialType instanceof InputTypeInterface) {
            $this->inputTypeStack[] = $initialType;
        } elseif ($initialType instanceof CompositeTypeInterface) {
            $this->parentTypeStack[] = $initialType;
        } elseif ($initialType instanceof OutputTypeInterface) {
            $this->typeStack[] = $initialType;
        }
    }

    /**
     * @param SchemaInterface $schema
     * @param TypeInterface   $parentType
     * @param FieldNode       $fieldNode
     * @return Field|null
     */
    public function resolveFieldDefinition(
        SchemaInterface $schema,
        TypeInterface $parentType,
        FieldNode $fieldNode
    ): ?Field {
        return $this->{$this->getFieldDefinitionFunction}($schema, $parentType, $fieldNode);
    }

    /**
     * @param OutputTypeInterface|null $type
     */
    public function pushType(?OutputTypeInterface $type): void
    {
        $this->typeStack[] = $type;
    }

    /**
     *
     */
    public function popType()
    {
        array_pop($this->typeStack);
    }

    /**
     * @return TypeInterface|OutputTypeInterface|null
     */
    public function getType(): ?OutputTypeInterface
    {
        return $this->getFromStack($this->typeStack, 1);
    }

    /**
     * @param CompositeTypeInterface|null $type
     */
    public function pushParentType(?CompositeTypeInterface $type): void
    {
        $this->parentTypeStack[] = $type;
    }

    /**
     *
     */
    public function popParentType()
    {
        array_pop($this->parentTypeStack);
    }

    /**
     * @return TypeInterface|CompositeTypeInterface|null
     */
    public function getParentType(): ?CompositeTypeInterface
    {
        return $this->getFromStack($this->parentTypeStack, 1);
    }

    /**
     * @param InputTypeInterface|null $type
     */
    public function pushInputType(?InputTypeInterface $type): void
    {
        $this->inputTypeStack[] = $type;
    }

    /**
     *
     */
    public function popInputType()
    {
        array_pop($this->inputTypeStack);
    }

    /**
     * @return TypeInterface|InputTypeInterface|null
     */
    public function getInputType(): ?InputTypeInterface
    {
        return $this->getFromStack($this->inputTypeStack, 1);
    }

    /**
     * @return TypeInterface|InputTypeInterface|null
     */
    public function getParentInputType(): ?InputTypeInterface
    {
        return $this->getFromStack($this->inputTypeStack, 2);
    }

    /**
     * @param Field|null $fieldDefinition
     */
    public function pushFieldDefinition(?Field $fieldDefinition): void
    {
        $this->fieldDefinitionStack[] = $fieldDefinition;
    }

    /**
     *
     */
    public function popFieldDefinition()
    {
        array_pop($this->fieldDefinitionStack);
    }

    /**
     * @return Field|null
     */
    public function getFieldDefinition(): ?Field
    {
        return $this->getFromStack($this->fieldDefinitionStack, 2);
    }

    /**
     * @return SchemaInterface
     */
    public function getSchema(): SchemaInterface
    {
        return $this->schema;
    }

    /**
     * @return Directive|null
     */
    public function getDirective(): ?Directive
    {
        return $this->directive;
    }

    /**
     * @param Directive|null $directive
     */
    public function setDirective(?Directive $directive): void
    {
        $this->directive = $directive;
    }

    /**
     * @return Argument|null
     */
    public function getArgument(): ?Argument
    {
        return $this->argument;
    }

    /**
     * @param Argument|null $argument
     */
    public function setArgument(?Argument $argument): void
    {
        $this->argument = $argument;
    }

    /**
     * @return EnumValue|null
     */
    public function getEnumValue(): ?EnumValue
    {
        return $this->enumValue;
    }

    /**
     * @param EnumValue|null $enumValue
     */
    public function setEnumValue(?EnumValue $enumValue): void
    {
        $this->enumValue = $enumValue;
    }

    /**
     * @param array $stack
     * @param int   $depth
     * @return mixed|null
     */
    protected function getFromStack(array $stack, int $depth)
    {
        $count = count($stack);
        if ($count > 0) {
            return $stack[$count - $depth];
        }
        return null;
    }
}

/**
 * @param SchemaInterface $schema
 * @param TypeInterface   $parentType
 * @param FieldNode       $fieldNode
 * @return Field|null
 * @throws \TypeError
 * @throws \Exception
 */
function getFieldDefinition(SchemaInterface $schema, TypeInterface $parentType, FieldNode $fieldNode): ?Field
{
    $name = $fieldNode->getNameValue();

    $schemaDefinition = SchemaMetaFieldDefinition();
    if ($name === $schemaDefinition->getName() && $schema->getQuery() === $parentType) {
        return $schemaDefinition;
    }

    $typeDefinition = TypeMetaFieldDefinition();
    if ($name === $typeDefinition->getName() && $schema->getQuery() === $parentType) {
        return $typeDefinition;
    }

    $typeNameDefinition = TypeNameMetaFieldDefinition();
    if ($name === $typeNameDefinition->getName() && $parentType instanceof CompositeTypeInterface) {
        return $typeNameDefinition;
    }

    if ($parentType instanceof ObjectType || $parentType instanceof InputTypeInterface) {
        return $parentType->getFields()[$name];
    }

    return null;
}
