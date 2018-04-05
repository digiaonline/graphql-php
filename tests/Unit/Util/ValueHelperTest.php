<?php

namespace Digia\GraphQL\Test\Unit\Util;

use Digia\GraphQL\Error\ResolutionException;
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
use Digia\GraphQL\Util\ValueHelper;
use function Digia\GraphQL\Type\Int;
use function Digia\GraphQL\Type\newEnumType;
use function Digia\GraphQL\Type\newInputObjectType;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newNonNull;
use function Digia\GraphQL\Type\String;

class ValueHelperTest extends TestCase
{
    /**
     * @var ValueHelper
     */
    protected $helper;

    public function setUp()
    {
        $this->helper = new ValueHelper();
    }

    public function testResolveNull()
    {
        $this->expectException(ResolutionException::class);
        $this->expectExceptionMessage('Node is not defined.');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->helper->fromAST(null, String());
    }

    public function testResolveNonNullWithStringValue()
    {
        $node = new StringValueNode(['value' => 'foo']);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals('foo', $this->helper->fromAST($node, newNonNull(String())));
    }

    public function testResolveNonNullWithNullValue()
    {
        $node = new NullValueNode();
        $this->expectException(ResolutionException::class);
        $this->expectExceptionMessage('Cannot resolve non-null values from null value node');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(null, $this->helper->fromAST($node, newNonNull(String())));
    }

    public function testResolveValidListOfStrings()
    {
        $node = new ListValueNode([
            'values' => [
                new StringValueNode(['value' => 'A']),
                new StringValueNode(['value' => 'B']),
                new StringValueNode(['value' => 'C']),
            ],
        ]);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(['A', 'B', 'C'], $this->helper->fromAST($node, newList(String())));
    }

    public function testResolveListWithMissingVariableValue()
    {
        $node = new ListValueNode([
            'values' => [
                new VariableNode(['name' => new NameNode(['value' => '$a'])]),
                new VariableNode(['name' => new NameNode(['value' => '$b'])]),
                new VariableNode(['name' => new NameNode(['value' => '$c'])]),
            ],
        ]);
        // Null-able inputs in a variable can be omitted
        $variables = ['$a' => 'A', '$c' => 'C'];
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(['A', null, 'C'], $this->helper->fromAST($node, newList(String()), $variables));
    }

    public function testResolveValidInputObject()
    {
        $node = new ObjectValueNode([
            'fields' => [
                new ObjectFieldNode([
                    'name'  => new NameNode(['value' => 'a']),
                    'value' => new IntValueNode(['value' => 1]),
                ]),
            ],
        ]);

        $type = newInputObjectType([
            'name'   => 'InputObject',
            'fields' => [
                'a' => ['type' => Int()],
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(['a' => 1], $this->helper->fromAST($node, $type));
    }

    public function testResolveInputObjectWithNodeOfInvalidType()
    {
        $node = new StringValueNode(['value' => null]);

        $type = newInputObjectType([
            'name'   => 'InputObject',
            'fields' => [
                'a' => ['type' => Int()],
            ],
        ]);

        $this->expectException(ResolutionException::class);
        $this->expectExceptionMessage('Input object values can only be resolved form object value nodes.');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(1, $this->helper->fromAST($node, $type));
    }

    public function testResolveInputObjectWithMissingNonNullField()
    {
        $node = new ObjectValueNode([
            'fields' => [
                new ObjectFieldNode([
                    'name'  => new NameNode(['value' => 'a']),
                    'value' => new IntValueNode(['value' => 1]),
                ]),
            ],
        ]);

        $type = newInputObjectType([
            'name'   => 'InputObject',
            'fields' => [
                'a' => ['type' => Int()],
                'b' => ['type' => newNonNull(String())],
            ],
        ]);

        $this->expectException(ResolutionException::class);
        $this->expectExceptionMessage('Cannot resolve input object value for missing non-null field.');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(['a' => 1], $this->helper->fromAST($node, $type));
    }

    public function testResolveEnumWithIntValue()
    {
        $node = new EnumValueNode(['value' => 'FOO']);
        $type = newEnumType([
            'name'   => 'EnumType',
            'values' => [
                'FOO' => ['value' => 1],
            ],
        ]);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(1, $this->helper->fromAST($node, $type));
    }

    public function testResolveEnumWithNodeOfInvalidType()
    {
        $node = new StringValueNode(['value' => null]);
        $type = newEnumType([
            'name' => 'EnumType',
        ]);

        $this->expectException(ResolutionException::class);
        $this->expectExceptionMessage('Enum values can only be resolved from enum value nodes.');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(1, $this->helper->fromAST($node, $type));
    }

    public function testResolveEnumWithMissingValue()
    {
        $node = new EnumValueNode(['value' => 'FOO']);
        $type = newEnumType([
            'name'   => 'EnumType',
            'values' => [
                'BAR' => ['value' => 'foo'],
            ],
        ]);
        $this->expectException(ResolutionException::class);
        $this->expectExceptionMessage('Cannot resolve enum value for missing value "FOO".');
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(1, $this->helper->fromAST($node, $type));
    }

    public function testResolveValidScalar()
    {
        $node = new StringValueNode(['value' => 'foo']);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals('foo', $this->helper->fromAST($node, String()));
    }

    public function testResolveInvalidScalar()
    {
        $node = new StringValueNode(['value' => null]);

        $this->expectException(ResolutionException::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals('foo', $this->helper->fromAST($node, String()));
    }
}
