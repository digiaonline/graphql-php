<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Type\Definition\BooleanType;
use Digia\GraphQL\Type\Definition\NonNullType;

class NonNullTypeTest extends TestCase
{

    /**
     * @throws \TypeError
     * @throws \Exception
     */
    public function testValidConstructor()
    {
        $type = new NonNullType(new BooleanType());

        $this->assertInstanceOf(BooleanType::class, $type->getOfType());
    }

    /**
     * @expectedException \TypeError
     */
    public function testInvalidConstructor()
    {
        new NonNullType(new NonNullType(new BooleanType()));
    }
}
