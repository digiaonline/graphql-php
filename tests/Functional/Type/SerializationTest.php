<?php

namespace Digia\GraphQL\Test\Functional\Type;

use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\Type\booleanType;
use function Digia\GraphQL\Type\floatType;
use function Digia\GraphQL\Type\intType;
use function Digia\GraphQL\Type\stringType;

class SerializationTest extends TestCase
{

    /**
     * @param mixed $value
     * @param mixed $answer
     * @dataProvider valuesIntCanRepresentDataProvider
     */
    public function testValuesIntCanRepresent($value, $answer)
    {
        $this->assertEquals($answer, intType()->serialize($value));
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
     * @param mixed $value
     * @dataProvider valuesIntCannotRepresentDataProvider
     * @expectedException \Digia\GraphQL\Error\InvalidTypeException
     */
    public function testValuesIntCannotRepresent($value)
    {
        intType()->serialize($value);
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
            [1e100],
            [-1e100],
            ['one'],
        ];
    }

    public function testSerializeBooleanToInt()
    {
        $this->assertEquals(0, intType()->serialize(false));
        $this->assertEquals(1, intType()->serialize(true));
    }

    /**
     * @expectedException \@expectedException \Digia\GraphQL\Error\InvalidTypeException
     */
    public function testSerializeEmptyStringToInt()
    {
        intType()->serialize('');
        $this->addToAssertionCount(1);
    }

    /**
     * @param mixed $value
     * @param mixed $answer
     * @dataProvider valuesFloatCanRepresentDataProvider
     */
    public function testValuesFloatCanRepresent($value, $answer)
    {
        $this->assertEquals($answer, floatType()->serialize($value));
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
     * @param mixed $value
     * @dataProvider valuesFloatCannotRepresentDataProvider
     * @expectedException \Digia\GraphQL\Error\InvalidTypeException
     */
    public function testValuesFloatCannotRepresent($value)
    {
        floatType()->serialize($value);
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
     * @param mixed $value
     * @param mixed $answer
     * @dataProvider valuesStringCanRepresentDataProvider
     */
    public function testValuesStringCanRepresent($value, $answer)
    {
        $this->assertEquals($answer, stringType()->serialize($value));
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
        $this->assertEquals($answer, booleanType()->serialize($value));
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
