<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Type\Definition\StringType;
use Digia\GraphQL\Type\Definition\TypeEnum;

class StringTypeTest extends TestCase
{

    /**
     * @throws \Exception
     */
    public function testGetName()
    {
        $type = new StringType();

        $this->assertEquals(TypeEnum::STRING, $type->getName());
    }

    /**
     * @throws \Exception
     * @dataProvider validValuesProvider
     */
    public function testSerializeValidValues($value, $expected)
    {
        $type = new StringType();

        $this->assertEquals($expected, $type->serialize($value));
    }

    /**
     * @throws \Exception
     * @dataProvider invalidValuesProvider
     * @expectedException \TypeError
     */
    public function testSerializeInvalidValues($value)
    {
        $type = new StringType();

        $type->serialize($value);
    }

    public function validValuesProvider(): array
    {
        return [
            'string value'  => ['foo', 'foo'],
            'integer value' => [42, '42'],
            'float value'   => [42.3, '42.3'],
            'null value'    => [null, 'null'],
            'true value'    => [true, 'true'],
            'false value'   => [false, 'false'],
        ];
    }

    public function invalidValuesProvider(): array
    {
        return [
            'array value' => [[4, 2, 3]],
        ];
    }
}
