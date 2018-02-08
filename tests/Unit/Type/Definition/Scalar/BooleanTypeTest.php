<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition\Scalar;

use Digia\GraphQL\Language\AST\Node\BooleanValueNode;
use Digia\GraphQL\Language\AST\Node\ObjectTypeDefinitionNode;
use Digia\GraphQL\Test\Unit\Type\Definition\AbstractTypeTestCase;
use Digia\GraphQL\Type\Definition\Scalar\BooleanType;
use Digia\GraphQL\Type\Definition\TypeEnum;

/**
 * Class BooleanTypeTest
 *
 * @package Digia\GraphQL\Test\Unit\Type\Definition
 * @property BooleanType $type
 */
class BooleanTypeTest extends AbstractTypeTestCase
{

    public function setUp()
    {
        $this->type = new BooleanType();
    }

    /**
     * @throws \Exception
     */
    public function testGetName()
    {
        $this->assertEquals(TypeEnum::BOOLEAN, $this->type->getName());
    }

    /**
     * @throws \Exception
     * @dataProvider validValuesProvider
     */
    public function testSerializeWithValidValues($value, $expected)
    {
        $this->assertEquals($expected, $this->type->serialize($value));
    }

    /**
     * @throws \Exception
     * @dataProvider invalidValuesProvider
     * @expectedException \TypeError
     */
    public function testSerializeWithInvalidValues($value)
    {
        $this->type->serialize($value);
    }

    /**
     * @throws \Exception
     * @dataProvider validValuesProvider
     */
    public function testParseValueWithValidValues($value, $expected)
    {
        $this->assertEquals($expected, $this->type->parseValue($value));
    }

    /**
     * @throws \Exception
     * @dataProvider invalidValuesProvider
     * @expectedException \TypeError
     */
    public function testParseValueWithInvalidValues($value)
    {
        $this->type->parseValue($value);
    }

    /**
     * @throws \Exception
     */
    public function testParseLiteralWithValidValues()
    {
        $this->assertEquals(true, $this->type->parseLiteral(new BooleanValueNode([
            'value' => true,
        ])));
    }

    /**
     * @throws \Exception
     */
    public function testParseLiteralWithInvalidValues()
    {
        $this->assertNull($this->type->parseLiteral(new ObjectTypeDefinitionNode()));
    }

    /**
     * @return array
     */
    public function validValuesProvider(): array
    {
        return [
            'true value'   => [true, true],
            'false value'  => [false, false],
            'string value' => ['foo', true],
            'int value'    => [1, true],
            'float value'  => [0.0, false],
        ];
    }

    /**
     * @return array
     */
    public function invalidValuesProvider(): array
    {
        return [
            'null value'         => [null],
            'empty string value' => [''],
        ];
    }
}
