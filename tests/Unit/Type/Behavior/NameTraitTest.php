<?php

namespace Digia\GraphQL\Test\Unit\Type\Behavior;

use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Behavior\ConfigTrait;
use Digia\GraphQL\Type\Behavior\NameTrait;

class NameTraitTest extends TestCase
{

    /**
     * @var NamedClass
     */
    protected $instance;

    public function setUp()
    {
        $this->instance = new NamedClass([
            'name' => 'Dummy',
        ]);
    }

    /**
     * @throws \Exception
     */
    public function testSetName()
    {
        $this->assertEquals('Dummy', $this->instance->getName());
    }

    /**
     * @throws \Exception
     */
    public function testToString()
    {
        $this->assertEquals('Dummy', (string)$this->instance);
    }
}

class NamedClass {
    use NameTrait;
    use ConfigTrait;
}
