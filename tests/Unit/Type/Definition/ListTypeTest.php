<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\Scalar\StringType;

class ListTypeTest extends TestCase
{

    /**
     * @throws \TypeError
     * @throws \Exception
     */
    public function testValidConstructor()
    {
        $type = new ListType(new StringType());

        $this->assertInstanceOf(StringType::class, $type->getOfType());
    }
}
