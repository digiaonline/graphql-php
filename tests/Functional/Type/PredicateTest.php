<?php

namespace Digia\GraphQL\Test\Functional\Type;

use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\UnionType;
use function Digia\GraphQL\Type\assertScalarType;
use function Digia\GraphQL\Type\assertType;
use function Digia\GraphQL\Type\newGraphQLEnumType;
use function Digia\GraphQL\Type\newGraphQLInputObjectType;
use function Digia\GraphQL\Type\newGraphQLInterfaceType;
use function Digia\GraphQL\Type\newGraphQLList;
use function Digia\GraphQL\Type\newGraphQLObjectType;
use function Digia\GraphQL\Type\newGraphQLScalarType;
use function Digia\GraphQL\Type\GraphQLString;
use function Digia\GraphQL\Type\newGraphQLUnionType;

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
        $this->objectType      = newGraphQLObjectType(['name' => 'Object']);
        $this->interfaceType   = newGraphQLInterfaceType(['name' => 'Interface']);
        $this->unionType       = newGraphQLUnionType(['name' => 'Union', 'types' => [$this->objectType]]);
        $this->enumType        = newGraphQLEnumType(['name' => 'Enum', 'values' => ['foo' => []]]);
        $this->inputObjectType = newGraphQLInputObjectType(['name' => 'InputObject']);
        $this->scalarType      = newGraphQLScalarType([
            'name'         => 'Scalar',
            'serialize'    => function () {
            },
            'parseValue'   => function () {
            },
            'parseLiteral' => function () {
            },
        ]);
    }

    public function testAssertType()
    {
        assertType(GraphQLString());
        assertType($this->objectType);

        $this->addToAssertionCount(2);
    }

    public function testAssertScalarTypeWithValidTypes()
    {
        assertScalarType(GraphQLString());
        assertScalarType($this->scalarType);

        $this->addToAssertionCount(2);
    }

    /**
     * @expectedException \Exception
     */
    public function testAssertScalarTypeWithInvalidTypes()
    {
        assertScalarType(newGraphQLList($this->scalarType));
        assertScalarType($this->enumType);
    }
}
