<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Type\Definition\ConfigTrait;
use Digia\GraphQL\Type\Definition\ValueTrait;

class ValueTraitTest extends TestCase
{

    /**
     * @var ValueClass
     */
    protected $instance;

    public function setUp()
    {
        $this->instance = new ValueClass([
            'value' => null,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function testHasValue()
    {
        $this->assertFalse($this->instance->hasValue());
    }

    /**
     * @throws \Exception
     */
    public function testGetValue()
    {
        $this->assertNull($this->instance->getValue());
    }
}

class ValueClass {
    use ValueTrait;
    use ConfigTrait;
}
