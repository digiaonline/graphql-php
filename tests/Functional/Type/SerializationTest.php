<?php

namespace Digia\GraphQL\Test\Functional\Type;

use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\Type\GraphQLBoolean;
use function Digia\GraphQL\Type\GraphQLFloat;
use function Digia\GraphQL\Type\GraphQLInt;
use function Digia\GraphQL\Type\GraphQLString;

class SerializationTest extends TestCase
{

    /**
     * @param mixed $value
     * @param mixed $answer
     * @throws \Exception
     * @dataProvider valuesIntCanRepresentDataProvider
     */
    public function testValuesIntCanRepresent($value, $answer)
    {
        $this->assertEquals($answer, GraphQLInt()->serialize($value));
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
     * @expectedException \TypeError
     */
    public function testValuesIntCannotRepresent($value)
    {
        GraphQLInt()->serialize($value);
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
            [9876504321],
            [-9876504321],
            [1e100],
            [-1e100],
            ['one'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function testSerializeBooleanToInt()
    {
        $this->assertEquals(0, GraphQLInt()->serialize(false));
        $this->assertEquals(1, GraphQLInt()->serialize(true));
    }

    /**
     * @expectedException \TypeError
     */
    public function testSerializeEmptyStringToInt()
    {
        GraphQLInt()->serialize('');
        $this->addToAssertionCount(1);
    }

    /**
     * @param mixed $value
     * @param mixed $answer
     * @throws \Exception
     * @dataProvider valuesFloatCanRepresentDataProvider
     */
    public function testValuesFloatCanRepresent($value, $answer)
    {
        $this->assertEquals($answer, GraphQLFloat()->serialize($value));
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
     * @expectedException \TypeError
     */
    public function testValuesFloatCannotRepresent($value)
    {
        GraphQLFloat()->serialize($value);
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
     * @throws \Exception
     * @dataProvider valuesStringCanRepresentDataProvider
     */
    public function testValuesStringCanRepresent($value, $answer)
    {
        $this->assertEquals($answer, GraphQLString()->serialize($value));
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
     * @throws \Exception
     * @dataProvider valuesBooleanCanRepresentDataProvider
     */
    public function testValuesBooleanCanRepresent($value, $answer)
    {
        $this->assertEquals($answer, GraphQLBoolean()->serialize($value));
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
