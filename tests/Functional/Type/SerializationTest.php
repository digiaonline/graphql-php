<?php

namespace Digia\GraphQL\Test\Functional\Type;

use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\Type\Boolean;
use function Digia\GraphQL\Type\Float;
use function Digia\GraphQL\Type\Int;
use function Digia\GraphQL\Type\String;

class SerializationTest extends TestCase
{

    /**
     * @param $value
     * @param $answer
     * @dataProvider valuesIntCanRepresentDataProvider
     */
    public function testValuesIntCanRepresent($value, $answer)
    {
        $this->assertEquals($answer, Int()->serialize($value));
    }

    /**
     * @return array
     */
    public function valuesIntCanRepresentDataProvider()
    {
        return [
            [1, 1],
            [123, 123],
            [0, 0],
            [-1, -1],
            [100000, 1e5],
        ];
    }

    /**
     * @param $value
     * @dataProvider valuesIntCannotRepresentDataProvider
     * @expectedException \@expectedException \Digia\GraphQL\Error\InvalidTypeException
     */
    public function testValuesIntCannotRepresent($value)
    {
        Int()->serialize($value);
        $this->addToAssertionCount(1);
    }

    /**
     * @return array
     */
    public function valuesIntCannotRepresentDataProvider()
    {
        return [
            [0.1],
            [1.1],
            [-1.1],
            ['-1.1'],
            [PHP_INT_MAX + 1],
            [PHP_INT_MIN - 1],
            [1e100],
            [-1e100],
            ['one'],
        ];
    }

    public function testSerializeBooleanToInt()
    {
        $this->assertEquals(0, Int()->serialize(false));
        $this->assertEquals(1, Int()->serialize(true));
    }

    /**
     * @expectedException \@expectedException \Digia\GraphQL\Error\InvalidTypeException
     */
    public function testSerializeEmptyStringToInt()
    {
        Int()->serialize('');
        $this->addToAssertionCount(1);
    }

    /**
     * @param $value
     * @param $answer
     * @dataProvider valuesFloatCanRepresentDataProvider
     */
    public function testValuesFloatCanRepresent($value, $answer)
    {
        $this->assertEquals($answer, Float()->serialize($value));
    }

    /**
     * @return array
     */
    public function valuesFloatCanRepresentDataProvider()
    {
        return [
            [1, 1.0],
            [0, 0.0],
            ['123.5', 123.5],
            [-1, -1.0],
            [0.1, 0.1],
            [1.1, 1.1],
            [-1.1, -1.1],
            ['-1.1', -1.1],
            [false, 0.0],
            [true, 1.0],
        ];
    }

    /**
     * @param $value
     * @dataProvider valuesFloatCannotRepresentDataProvider
     * @expectedException \Digia\GraphQL\Error\InvalidTypeException
     */
    public function testValuesFloatCannotRepresent($value)
    {
        Float()->serialize($value);
        $this->addToAssertionCount(1);
    }

    /**
     * @return array
     */
    public function valuesFloatCannotRepresentDataProvider()
    {
        return [['one'], ['']];
    }

    /**
     * @param $value
     * @param $answer
     * @dataProvider valuesStringCanRepresentDataProvider
     */
    public function testValuesStringCanRepresent($value, $answer)
    {
        $this->assertEquals($answer, String()->serialize($value));
    }

    /**
     * @return array
     */
    public function valuesStringCanRepresentDataProvider()
    {
        return [
            ['string', 'string'],
            [1, '1'],
            [-1.1, '-1.1'],
            [true, 'true'],
            [false, 'false'],
        ];
    }

    /**
     * @dataProvider valuesBooleanCanRepresentDataProvider
     */
    public function testValuesBooleanCanRepresent($value, $answer)
    {
        $this->assertEquals($answer, Boolean()->serialize($value));
    }

    /**
     * @return array
     */
    public function valuesBooleanCanRepresentDataProvider()
    {
        return [
            ['string', true],
            ['', false],
            [1, true],
            [0, false],
            [true, true],
            [false, false],
        ];
    }
}
