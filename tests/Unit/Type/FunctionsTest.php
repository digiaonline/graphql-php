<?php

namespace Digia\GraphQL\Test\Unit\Type;

use Digia\GraphQL\Test\Unit\TestCase;
use function Digia\GraphQL\Type\assertAbstractType;
use function Digia\GraphQL\Type\assertCompositeType;
use function Digia\GraphQL\Type\assertEnumType;
use function Digia\GraphQL\Type\assertInputObjectType;
use function Digia\GraphQL\Type\assertInputType;
use function Digia\GraphQL\Type\assertInterfaceType;
use function Digia\GraphQL\Type\assertLeafType;
use function Digia\GraphQL\Type\assertListType;
use function Digia\GraphQL\Type\assertNamedType;
use function Digia\GraphQL\Type\assertNonNullType;
use function Digia\GraphQL\Type\assertNullableType;
use function Digia\GraphQL\Type\assertObjectType;
use function Digia\GraphQL\Type\assertOutputType;
use function Digia\GraphQL\Type\assertScalarType;
use function Digia\GraphQL\Type\assertType;
use function Digia\GraphQL\Type\assertUnionType;
use function Digia\GraphQL\Type\assertWrappingType;
use Digia\GraphQL\Type\Definition\Contract\TypeInterface;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\Scalar\BooleanType;
use Digia\GraphQL\Type\Definition\Scalar\FloatType;
use Digia\GraphQL\Type\Definition\Scalar\IDType;
use Digia\GraphQL\Type\Definition\Scalar\IntType;
use Digia\GraphQL\Type\Definition\Scalar\StringType;
use Digia\GraphQL\Type\Definition\UnionType;
use Digia\GraphQL\Type\Directive\AbstractDirective;
use Digia\GraphQL\Type\Directive\DeprecatedDirective;
use Digia\GraphQL\Type\Directive\IncludeDirective;
use Digia\GraphQL\Type\Directive\SkipDirective;
use function Digia\GraphQL\Type\isAbstractType;
use function Digia\GraphQL\Type\isCompositeType;
use function Digia\GraphQL\Type\isDirective;
use function Digia\GraphQL\Type\isEnumType;
use function Digia\GraphQL\Type\isInputObjectType;
use function Digia\GraphQL\Type\isInputType;
use function Digia\GraphQL\Type\isInterfaceType;
use function Digia\GraphQL\Type\isLeafType;
use function Digia\GraphQL\Type\isListType;
use function Digia\GraphQL\Type\isNamedType;
use function Digia\GraphQL\Type\isNonNullType;
use function Digia\GraphQL\Type\isNullableType;
use function Digia\GraphQL\Type\isOutputType;
use function Digia\GraphQL\Type\isPlainObj;
use function Digia\GraphQL\Type\isSpecifiedDirective;
use function Digia\GraphQL\Type\isSpecifiedScalarType;
use function Digia\GraphQL\Type\isWrappingType;

class FunctionsTest extends TestCase
{

    /**
     * @throws \Exception
     */
    public function testIsPlainObj()
    {
        $this->assertTrue(isPlainObj(new NonTypeClass()));
        $this->assertFalse(isPlainObj('foo'));
        $this->assertFalse(isPlainObj([42]));
    }

    /**
     * @throws \Exception
     */
    public function testIsDirective()
    {
        $this->assertTrue(isDirective(new IncludeDirective()));
        $this->assertFalse(isDirective(new NonTypeClass()));
    }

    /**
     * @throws \Exception
     */
    public function testAssertTypeWithValidType()
    {
        assertType(new StringType());
        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testAssertTypeWithInvalidType()
    {
        assertType(new NonTypeClass());
    }

    /**
     * @throws \Exception
     */
    public function testAssertScalarTypeWithValidType()
    {
        assertScalarType(new BooleanType());
        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testAssertScalarTypeWithInvalidType()
    {
        assertScalarType(new ObjectType());
    }

    /**
     * @throws \Exception
     */
    public function testAssertObjectTypeWithValidType()
    {
        assertObjectType(new ObjectType());
        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testAssertObjectTypeWithInvalidType()
    {
        assertObjectType(new StringType());
    }

    /**
     * @throws \Exception
     */
    public function testAssertInterfaceTypeWithValidType()
    {
        assertInterfaceType(new InterfaceType());
        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testAssertInterfaceTypeWithInvalidType()
    {
        assertInterfaceType(new StringType());
    }

    /**
     * @throws \Exception
     */
    public function testAssertUnionTypeWithValidType()
    {
        assertUnionType(new UnionType());
        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testAssertUnionTypeWithInvalidType()
    {
        assertUnionType(new StringType());
    }

    /**
     * @throws \Exception
     */
    public function testAssertEnumTypeWithValidType()
    {
        assertEnumType(new EnumType());
        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testAssertEnumTypeWithInvalidType()
    {
        assertEnumType(new StringType());
    }

    /**
     * @throws \Exception
     */
    public function testAssertInputObjectTypeWithValidType()
    {
        assertInputObjectType(new InputObjectType());
        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testAssertInputObjectTypeWithInvalidType()
    {
        assertInputObjectType(new StringType());
    }

    /**
     * @throws \Exception
     * @throws \TypeError
     */
    public function testAssertListTypeWithValidType()
    {
        assertListType(new ListType(new StringType()));
        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testAssertListTypeWithInvalidType()
    {
        assertListType(new StringType());
    }

    /**
     * @throws \Exception
     * @throws \TypeError
     */
    public function testAssertNonNullTypeWithValidType()
    {
        assertNonNullType(new NonNullType(new StringType()));
        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testAssertNonNullTypeWithInvalidType()
    {
        assertNonNullType(new StringType());
    }

    /**
     * @throws \Exception
     * @throws \TypeError
     */
    public function testAssertInputTypeWithValidType()
    {
        assertInputType(new BooleanType());
        assertInputType(new FloatType());
        assertInputType(new IntType());
        assertInputType(new IDType());
        assertInputType(new StringType());
        assertInputType(new EnumType());
        assertInputType(new InputObjectType());
        assertInputType(new ListType(new StringType()));
        $this->addToAssertionCount(8);
    }

    /**
     * @expectedException \Exception
     */
    public function testAssertInputTypeWithInvalidType()
    {
        assertInputType(new ObjectType());
    }

    /**
     * @throws \Exception
     * @throws \TypeError
     */
    public function testAssertOutputTypeWithValidType()
    {
        assertOutputType(new BooleanType());
        assertOutputType(new FloatType());
        assertOutputType(new IntType());
        assertOutputType(new IDType());
        assertOutputType(new StringType());
        assertOutputType(new EnumType());
        assertOutputType(new InterfaceType());
        assertOutputType(new ObjectType());
        assertOutputType(new UnionType());
        assertOutputType(new ListType(new StringType()));
        $this->addToAssertionCount(10);
    }

    /**
     * @expectedException \Exception
     */
    public function testAssertOutputTypeWithInvalidType()
    {
        assertOutputType(new TypeClass());
    }

    /**
     * @throws \Exception
     */
    public function testAssertLeafTypeWithValidType()
    {
        assertLeafType(new BooleanType());
        assertLeafType(new FloatType());
        assertLeafType(new IntType());
        assertLeafType(new IDType());
        assertLeafType(new StringType());
        assertLeafType(new EnumType());
        $this->addToAssertionCount(6);
    }

    /**
     * @expectedException \Exception
     */
    public function testAssertLeafTypeWithInvalidType()
    {
        assertLeafType(new ObjectType());
    }

    /**
     * @throws \Exception
     */
    public function testAssertCompositeTypeWithValidType()
    {
        assertCompositeType(new InterfaceType());
        assertCompositeType(new ObjectType());
        assertCompositeType(new UnionType());
        $this->addToAssertionCount(3);
    }

    /**
     * @expectedException \Exception
     */
    public function testAssertCompositeTypeWithInvalidType()
    {
        assertCompositeType(new EnumType());
    }

    /**
     * @throws \Exception
     */
    public function testAssertAbstractTypeWithValidType()
    {
        assertAbstractType(new InterfaceType());
        assertAbstractType(new UnionType());
        $this->addToAssertionCount(2);
    }

    /**
     * @expectedException \Exception
     */
    public function testAssertAbstractTypeWithInvalidType()
    {
        assertAbstractType(new ObjectType());
    }

    /**
     * @throws \Exception
     * @throws \TypeError
     */
    public function testAssertWrappingTypeWithValidType()
    {
        assertWrappingType(new ListType(new StringType()));
        assertWrappingType(new NonNullType(new BooleanType()));
        $this->addToAssertionCount(2);
    }

    /**
     * @expectedException \Exception
     */
    public function testAssertWrappingTypeWithInvalidType()
    {
        assertWrappingType(new ObjectType());
    }

    /**
     * @throws \Exception
     * @throws \TypeError
     */
    public function testAssertNullableTypeWithValidType()
    {
        assertNullableType(new StringType());
        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     * @throws \TypeError
     */
    public function testAssertNullableTypeWithInvalidType()
    {
        assertNullableType(new NonNullType(new StringType()));
    }

    /**
     * @throws \Exception
     */
    public function testAssertNamedTypeWithValidType()
    {
        assertNamedType(new BooleanType());
        assertNamedType(new FloatType());
        assertNamedType(new IntType());
        assertNamedType(new IDType());
        assertNamedType(new StringType());
        assertNamedType(new ObjectType());
        $this->addToAssertionCount(6);
    }

    /**
     * @expectedException \Exception
     */
    public function testAssertNamedTypeWithInvalidType()
    {
        assertNamedType(new EnumType());
    }

    /**
     * @throws \Exception
     */
    public function testIsSpecifiedScalarType()
    {
        $this->assertTrue(isSpecifiedScalarType(new BooleanType()));
        $this->assertTrue(isSpecifiedScalarType(new FloatType()));
        $this->assertTrue(isSpecifiedScalarType(new IntType()));
        $this->assertTrue(isSpecifiedScalarType(new IDType()));
        $this->assertTrue(isSpecifiedScalarType(new StringType()));
        $this->assertFalse(isSpecifiedScalarType(new ObjectType()));
    }

    /**
     * @throws \Exception
     */
    public function testIsSpecifiedDirective()
    {
        $this->assertTrue(isSpecifiedDirective(new IncludeDirective()));
        $this->assertTrue(isSpecifiedDirective(new SkipDirective()));
        $this->assertTrue(isSpecifiedDirective(new DeprecatedDirective()));
        $this->assertFalse(isSpecifiedDirective(new DirectiveClass()));
    }
}

class NonTypeClass {
}

class TypeClass implements TypeInterface
{

    public function getName(): ?string
    {
        return 'TypeClass';
    }

    public function getDescription(): ?string
    {
        return 'A type class.';
    }

    public function __toString(): string
    {
        return '';
    }
}

class DirectiveClass extends AbstractDirective
{
}
