<?php

namespace Digia\GraphQL\Test\Unit\Type;

use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Type\Directive\IncludeDirective;
use function Digia\GraphQL\Type\isDirective;
use function Digia\GraphQL\Type\isPlainObj;

class FunctionsTest extends TestCase
{

    /**
     * @throws \Exception
     */
    public function testIsPlainObj()
    {
        $this->assertTrue(isPlainObj(new Plain()));
        $this->assertFalse(isPlainObj('foo'));
        $this->assertFalse(isPlainObj([42]));
    }

    /**
     * @throws \Exception
     */
    public function testIsDirective()
    {
        $this->assertTrue(isDirective(new IncludeDirective()));
        $this->assertFalse(isDirective(new Plain()));
    }
}

class Plain {

}
