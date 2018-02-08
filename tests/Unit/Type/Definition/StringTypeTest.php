<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Language\AST\Node\ObjectTypeDefinitionNode;
use Digia\GraphQL\Language\AST\Node\StringDefinitionNode;
use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Type\Definition\StringType;
use Digia\GraphQL\Type\Definition\TypeEnum;

/**
 * Class StringTypeTest
 *
 * @package Digia\GraphQL\Test\Unit\Type\Definition
 * @property StringType $type
 */
class StringTypeTest extends AbstractTypeTestCase
{

    public function setUp()
    {
        $this->type = new StringType();
    }

    /**
     * @throws \Exception
     */
    public function testGetName()
    {
        $this->assertEquals(TypeEnum::STRING, $this->type->getName());
    }

    /**
     * @throws \Exception
     * @dataProvider validValuesProvider
     */
    public function testSerializeValidValues($value, $expected)
    {
        $this->assertEquals($expected, $this->type->serialize($value));
    }

    /**
     * @throws \Exception
     * @dataProvider invalidValuesProvider
     * @expectedException \TypeError
     */
    public function testSerializeInvalidValues($value)
    {
        $this->type->serialize($value);
    }

    /**
     * @throws \Exception
     * @dataProvider validValuesProvider
     */
    public function testParseValidValues($value, $expected)
    {
        $this->assertEquals($expected, $this->type->parseValue($value));
    }

    /**
     * @throws \Exception
     * @dataProvider invalidValuesProvider
     * @expectedException \TypeError
     */
    public function testParseInvalidValues($value)
    {
        $this->type->parseValue($value);
    }

    /**
     * @throws \Exception
     */
    public function testParseLiteralWithValidValues()
    {
        $this->assertEquals('foo', $this->type->parseLiteral(new StringDefinitionNode([
            'value' => 'foo',
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
            'string value'  => ['foo', 'foo'],
            'integer value' => [42, '42'],
            'float value'   => [42.3, '42.3'],
            'null value'    => [null, 'null'],
            'true value'    => [true, 'true'],
            'false value'   => [false, 'false'],
        ];
    }

    /**
     * @return array
     */
    public function invalidValuesProvider(): array
    {
        return [
            'array value' => [[4, 2, 3]],
        ];
    }
}
