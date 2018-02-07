<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Type\Definition\BooleanType;
use Digia\GraphQL\Type\Definition\TypeEnum;

class BooleanTypeTest extends TestCase
{

    /**
     * @throws \Exception
     */
    public function testGetName()
    {
        $type = new BooleanType();

        $this->assertEquals(TypeEnum::BOOLEAN, $type->getName());
    }

    /**
     * @throws \Exception
     * @dataProvider validValuesProvider
     */
    public function testSerializeWithValidValues($value, $expected)
    {
        $type = new BooleanType();

        $this->assertEquals($expected, $type->serialize($value));
    }

    /**
     * @throws \Exception
     * @dataProvider invalidValuesProvider
     * @expectedException \TypeError
     */
    public function testSerializeWithInvalidValues($value)
    {
        $type = new BooleanType();

        $type->serialize($value);
    }

    /**
     * @throws \Exception
     * @dataProvider validValuesProvider
     */
    public function testParseValueWithValidValues($value, $expected)
    {
        $type = new BooleanType();

        $this->assertEquals($expected, $type->parseValue($value));
    }

    /**
     * @throws \Exception
     * @dataProvider invalidValuesProvider
     * @expectedException \TypeError
     */
    public function testParseValueWithInvalidValues($value)
    {
        $type = new BooleanType();

        $type->parseValue($value);
    }

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

    public function invalidValuesProvider(): array
    {
        return [
            'null value'   => [null],
        ];
    }
}
