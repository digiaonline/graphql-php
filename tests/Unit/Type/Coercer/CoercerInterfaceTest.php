<?php

namespace Digia\GraphQL\Test\Unit\Type\Coercer;

use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Coercer\BooleanCoercer;
use Digia\GraphQL\Type\Coercer\CoercerInterface;
use Digia\GraphQL\Type\Coercer\FloatCoercer;
use Digia\GraphQL\Type\Coercer\IntCoercer;
use Digia\GraphQL\Type\Coercer\StringCoercer;

class CoercerInterfaceTest extends TestCase
{
    public function testCoercersImplementsThisInterface()
    {
        $this->assertInstanceOf(CoercerInterface::class, new IntCoercer());
        $this->assertInstanceOf(CoercerInterface::class, new FloatCoercer());
        $this->assertInstanceOf(CoercerInterface::class, new StringCoercer());
        $this->assertInstanceOf(CoercerInterface::class, new BooleanCoercer());
    }
}