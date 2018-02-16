<?php

namespace Digia\GraphQL\Test\Functional\Type;

use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\Type\assertScalarType;
use function Digia\GraphQL\Type\assertType;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\UnionType;
use function Digia\GraphQL\Type\GraphQLEnumType;
use function Digia\GraphQL\Type\GraphQLInputObjectType;
use function Digia\GraphQL\Type\GraphQLInterfaceType;
use function Digia\GraphQL\Type\GraphQLList;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLScalarType;
use function Digia\GraphQL\Type\GraphQLString;
use function Digia\GraphQL\Type\GraphQLUnionType;

class PredicateTest extends TestCase
{

    /**
     * @var ObjectType
     */
    protected $objectType;

    /**
     * @var InterfaceType
     */
    protected $interfaceType;

    /**
     * @var UnionType
     */
    protected $unionType;

    /**
     * @var EnumType
     */
    protected $enumType;

    /**
     * @var InputObjectType
     */
    protected $inputObjectType;

    /**
     * @var ScalarType
     */
    protected $scalarType;

    public function setUp()
    {
        $this->objectType      = GraphQLObjectType(['name' => 'Object']);
        $this->interfaceType   = GraphQLInterfaceType(['name' => 'Interface']);
        $this->unionType       = GraphQLUnionType(['name' => 'Union', 'types' => [$this->objectType]]);
        $this->enumType        = GraphQLEnumType(['name' => 'Enum', 'values' => ['foo' => []]]);
        $this->inputObjectType = GraphQLInputObjectType(['name' => 'InputObject']);
        $this->scalarType      = GraphQLScalarType([
            'name'         => 'Scalar',
            'serialize'    => function () {
            },
            'parseValue'   => function () {
            },
            'parseLiteral' => function () {
            },
        ]);
    }

    /**
     * @throws \Exception
     */
    public function testAssertType()
    {
        assertType(GraphQLString());
        assertType($this->objectType);

        $this->addToAssertionCount(2);
    }

    /**
     * @throws \Exception
     * @throws \TypeError
     */
    public function testAssertScalarTypeWithValidTypes()
    {
        assertScalarType(GraphQLString());
        assertScalarType($this->scalarType);

        $this->addToAssertionCount(2);
    }

    /**
     * @throws \TypeError
     * @expectedException \Exception
     */
    public function testAssertScalarTypeWithInvalidTypes()
    {
        assertScalarType(GraphQLList($this->scalarType));
        assertScalarType($this->enumType);
    }
}
