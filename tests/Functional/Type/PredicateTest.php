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
use function Digia\GraphQL\Type\newEnumType;
use function Digia\GraphQL\Type\newInputObjectType;
use function Digia\GraphQL\Type\newInterfaceType;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newScalarType;
use function Digia\GraphQL\Type\String;
use function Digia\GraphQL\Type\newUnionType;

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
        $this->objectType      = newObjectType(['name' => 'Object']);
        $this->interfaceType   = newInterfaceType(['name' => 'Interface']);
        $this->unionType       = newUnionType(['name' => 'Union', 'types' => [$this->objectType]]);
        $this->enumType        = newEnumType(['name' => 'Enum', 'values' => ['foo' => []]]);
        $this->inputObjectType = newInputObjectType(['name' => 'InputObject']);
        $this->scalarType      = newScalarType([
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
        assertType(String());
        assertType($this->objectType);

        $this->addToAssertionCount(2);
    }

    public function testAssertScalarTypeWithValidTypes()
    {
        assertScalarType(String());
        assertScalarType($this->scalarType);

        $this->addToAssertionCount(2);
    }

    /**
     * @expectedException \Exception
     */
    public function testAssertScalarTypeWithInvalidTypes()
    {
        assertScalarType(newList($this->scalarType));
        assertScalarType($this->enumType);
    }
}
