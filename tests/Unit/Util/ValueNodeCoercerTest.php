<?php

namespace Digia\GraphQL\Test\Unit\Util;

use Digia\GraphQL\Error\CoercingException;
use Digia\GraphQL\Language\Node\EnumValueNode;
use Digia\GraphQL\Language\Node\IntValueNode;
use Digia\GraphQL\Language\Node\ListValueNode;
use Digia\GraphQL\Language\Node\NameNode;
use Digia\GraphQL\Language\Node\NullValueNode;
use Digia\GraphQL\Language\Node\ObjectFieldNode;
use Digia\GraphQL\Language\Node\ObjectValueNode;
use Digia\GraphQL\Language\Node\StringValueNode;
use Digia\GraphQL\Language\Node\VariableNode;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Util\ValueNodeCoercer;
use function Digia\GraphQL\Type\GraphQLEnumType;
use function Digia\GraphQL\Type\GraphQLInputObjectType;
use function Digia\GraphQL\Type\GraphQLInt;
use function Digia\GraphQL\Type\GraphQLList;
use function Digia\GraphQL\Type\GraphQLNonNull;
use function Digia\GraphQL\Type\GraphQLString;

class ValueNodeCoercerTest extends TestCase
{
    /**
     * @var ValueNodeCoercer
     */
    protected $coercer;

    public function setUp()
    {
        $this->coercer = new ValueNodeCoercer();
    }

    public function testCoerceNull()
    {
        $this->expectException(CoercingException::class);
        $this->expectExceptionMessage('Node is not defined.');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->coercer->coerce(null, GraphQLString());
    }

    public function testCoerceNonNullWithStringValue()
    {
        $node = new StringValueNode(['value' => 'foo']);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals('foo', $this->coercer->coerce($node, GraphQLNonNull(GraphQLString())));
    }

    public function testCoerceNonNullWithNullValue()
    {
        $node = new NullValueNode();
        $this->expectException(CoercingException::class);
        $this->expectExceptionMessage('Cannot coerce non-null values from null value node');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(null, $this->coercer->coerce($node, GraphQLNonNull(GraphQLString())));
    }

    public function testCoerceValidListOfStrings()
    {
        $node = new ListValueNode([
            'values' => [
                new StringValueNode(['value' => 'A']),
                new StringValueNode(['value' => 'B']),
                new StringValueNode(['value' => 'C']),
            ],
        ]);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(['A', 'B', 'C'], $this->coercer->coerce($node, GraphQLList(GraphQLString())));
    }

    public function testCoerceListWithMissingVariableValue()
    {
        $node      = new ListValueNode([
            'values' => [
                new VariableNode(['name' => new NameNode(['value' => '$a'])]),
                new VariableNode(['name' => new NameNode(['value' => '$b'])]),
                new VariableNode(['name' => new NameNode(['value' => '$c'])]),
            ],
        ]);
        $variables = ['$a' => 'A', '$c' => 'C'];
        $this->expectException(CoercingException::class);
        $this->expectExceptionMessage('Cannot coerce value for missing variable "$b".');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(['A', 'B', 'C'], $this->coercer->coerce($node, GraphQLList(GraphQLString()), $variables));
    }

    public function testCoerceValidInputObject()
    {
        $node = new ObjectValueNode([
            'fields' => [
                new ObjectFieldNode([
                    'name'  => new NameNode(['value' => 'a']),
                    'value' => new IntValueNode(['value' => 1]),
                ]),
            ],
        ]);

        $type = GraphQLInputObjectType([
            'name'   => 'InputObject',
            'fields' => [
                'a' => ['type' => GraphQLInt()],
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(['a' => 1], $this->coercer->coerce($node, $type));
    }

    public function testCoerceInputObjectWithNodeOfInvalidType()
    {
        $node = new StringValueNode(['value' => null]);

        $type = GraphQLInputObjectType([
            'name'   => 'InputObject',
            'fields' => [
                'a' => ['type' => GraphQLInt()],
            ],
        ]);

        $this->expectException(CoercingException::class);
        $this->expectExceptionMessage('Input object values can only be coerced form object value nodes.');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(1, $this->coercer->coerce($node, $type));
    }

    public function testCoerceInputObjectWithMissingNonNullField()
    {
        $node = new ObjectValueNode([
            'fields' => [
                new ObjectFieldNode([
                    'name'  => new NameNode(['value' => 'a']),
                    'value' => new IntValueNode(['value' => 1]),
                ]),
            ],
        ]);

        $type = GraphQLInputObjectType([
            'name'   => 'InputObject',
            'fields' => [
                'a' => ['type' => GraphQLInt()],
                'b' => ['type' => GraphQLNonNull(GraphQLString())],
            ],
        ]);

        $this->expectException(CoercingException::class);
        $this->expectExceptionMessage('Cannot coerce input object value for missing non-null field.');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(['a' => 1], $this->coercer->coerce($node, $type));
    }

    public function testCoerceEnumWithIntValue()
    {
        $node = new EnumValueNode(['value' => 'FOO']);
        $type = GraphQLEnumType([
            'name'   => 'EnumType',
            'values' => [
                'FOO' => ['value' => 1],
            ],
        ]);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(1, $this->coercer->coerce($node, $type));
    }

    public function testCoerceEnumWithNodeOfInvalidType()
    {
        $node = new StringValueNode(['value' => null]);
        $type = GraphQLEnumType([
            'name'   => 'EnumType',
        ]);

        $this->expectException(CoercingException::class);
        $this->expectExceptionMessage('Enum values can only be coerced from enum value nodes.');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(1, $this->coercer->coerce($node, $type));
    }

    public function testCoerceEnumWithMissingValue()
    {
        $node = new EnumValueNode(['value' => 'FOO']);
        $type = GraphQLEnumType([
            'name'   => 'EnumType',
            'values' => [
                'BAR' => ['value' => 'foo'],
            ],
        ]);
        $this->expectException(CoercingException::class);
        $this->expectExceptionMessage('Cannot coerce enum value for missing value "FOO".');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(1, $this->coercer->coerce($node, $type));
    }

    public function testCoerceValidScalar()
    {
        $node = new StringValueNode(['value' => 'foo']);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals('foo', $this->coercer->coerce($node, GraphQLString()));
    }

    public function testCoerceInvalidScalar()
    {
        $node = new StringValueNode(['value' => null]);

        $this->expectException(CoercingException::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals('foo', $this->coercer->coerce($node, GraphQLString()));
    }
}
