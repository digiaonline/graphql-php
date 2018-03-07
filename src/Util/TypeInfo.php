<?php

namespace Digia\GraphQL\Util;

use Digia\GraphQL\Type\Definition\Argument;
use Digia\GraphQL\Type\Definition\CompositeTypeInterface;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\EnumValue;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\InputTypeInterface;
use Digia\GraphQL\Type\Definition\OutputTypeInterface;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\SchemaInterface;

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
        ?callable $getFieldDefinitionFunction,
        ?TypeInterface $initialType = null
    ) {
        $this->schema                     = $schema;
        $this->getFieldDefinitionFunction = $getFieldDefinitionFunction;

        if ($initialType instanceof InputTypeInterface) {
            $this->inputTypeStack[] = $initialType;
        } elseif ($initialType instanceof CompositeTypeInterface) {
            $this->parentTypeStack[] = $initialType;
        } elseif ($initialType instanceof OutputTypeInterface) {
            $this->typeStack[] = $initialType;
        }
    }

    /**
     * @return OutputTypeInterface|null
     */
    public function getType(): ?OutputTypeInterface
    {
        return $this->getFromStack($this->typeStack, 1);
    }

    /**
     * @return CompositeTypeInterface|null
     */
    public function getParentType(): ?CompositeTypeInterface
    {
        return $this->getFromStack($this->parentTypeStack, 1);
    }

    /**
     * @return InputTypeInterface|null
     */
    public function getInputType(): ?InputTypeInterface
    {
        return $this->getFromStack($this->inputTypeStack, 1);
    }

    /**
     * @return InputTypeInterface|null
     */
    public function getParentInputType(): ?InputTypeInterface
    {
        return $this->getFromStack($this->inputTypeStack, 2);
    }

    /**
     * @return Field|null
     */
    public function getFieldDefinition(): ?Field
    {
        return $this->getFromStack($this->fieldDefinitionStack, 2);
    }

    /**
     * @return Directive|null
     */
    public function getDirective(): ?Directive
    {
        return $this->directive;
    }

    /**
     * @return Argument|null
     */
    public function getArgument(): ?Argument
    {
        return $this->argument;
    }

    /**
     * @return EnumValue|null
     */
    public function getEnumValue(): ?EnumValue
    {
        return $this->enumValue;
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
