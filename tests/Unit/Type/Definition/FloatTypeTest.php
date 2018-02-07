<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Type\Definition\FloatType;
use Digia\GraphQL\Type\Definition\TypeEnum;

class FloatTypeTest extends TestCase
{

    /**
     * @throws \Exception
     */
    public function testGetName()
    {
        $type = new FloatType();

        $this->assertEquals(TypeEnum::FLOAT, $type->getName());
    }

    /**
     * @throws \Exception
     * @dataProvider validValuesProvider
     */
    public function testSerializeValidValues($value, $expected)
    {
        $type = new FloatType();

        $this->assertEquals($expected, $type->serialize($value));
    }

    /**
     * @throws \Exception
     * @dataProvider invalidValuesProvider
     * @expectedException \TypeError
     */
    public function testSerializeInvalidValues($value)
    {
        $type = new FloatType();

        $type->serialize($value);
    }

    public function validValuesProvider(): array
    {
        return [
            'integer value' => [42, 42.0],
            'string value'  => ['42.3', 42.3],
        ];
    }

    public function invalidValuesProvider(): array
    {
        return [
            'non-numeric string value' => ['foo'],
            'null value'               => [null],
        ];
    }
}
