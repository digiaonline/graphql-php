<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Type\Definition\IntType;
use Digia\GraphQL\Type\Definition\TypeEnum;

class IntTypeTest extends TestCase
{

    /**
     * @throws \Exception
     */
    public function testGetName()
    {
        $type = new IntType();

        $this->assertEquals(TypeEnum::INT, $type->getName());
    }

    /**
     * @throws \Exception
     * @dataProvider validValuesProvider
     */
    public function testSerializeValidValues($value, $expected)
    {
        $type = new IntType();

        $this->assertEquals($expected, $type->serialize($value));
    }

    /**
     * @throws \Exception
     * @dataProvider invalidValuesProvider
     * @expectedException \TypeError
     */
    public function testSerializeInvalidValues($value)
    {
        $type = new IntType();

        $type->serialize($value);
    }

    public function validValuesProvider(): array
    {
        return [
            'integer value' => [42, 42],
            'string value'  => ['42', 42],
        ];
    }

    public function invalidValuesProvider(): array
    {
        return [
            'float value'   => [42.5],
            'null value'    => [null],
        ];
    }
}
