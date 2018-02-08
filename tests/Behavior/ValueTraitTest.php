<?php

namespace Digia\GraphQL\Test\Unit\Behavior;

use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Behavior\ConfigTrait;
use Digia\GraphQL\Behavior\ValueTrait;

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
