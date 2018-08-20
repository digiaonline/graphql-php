<?php

namespace Digia\GraphQL\Test\Functional\Type;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\UnionType;
use function Digia\GraphQL\Type\assertType;
use function Digia\GraphQL\Type\newEnumType;
use function Digia\GraphQL\Type\newInputObjectType;
use function Digia\GraphQL\Type\newInterfaceType;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newScalarType;
use function Digia\GraphQL\Type\newUnionType;
use function Digia\GraphQL\Type\stringType;
use function Digia\GraphQL\Util\toString;

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

    /**
     * @inheritdoc
     * 
     * @throws InvariantException
     */
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

    /**
     * @throws InvariantException
     */
    public function testAssertType()
    {
        assertType(stringType());
        assertType($this->objectType);

        $this->addToAssertionCount(2);
    }

    /**
     * @throws InvariantException
     */
    public function testAssertScalarTypeWithValidTypes()
    {
        assertScalarType(stringType());
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

/**
 * @param mixed $type
 * @throws InvariantException
 */
function assertScalarType($type)
{
    if (!($type instanceof ScalarType)) {
        throw new InvariantException(\sprintf('Expected %s to be a GraphQL Scalar type.', toString($type)));
    }
}
