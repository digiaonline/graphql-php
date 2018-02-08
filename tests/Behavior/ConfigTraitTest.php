<?php

namespace Digia\GraphQL\Test\Unit\Behavior;

use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Behavior\ConfigTrait;

class ConfigTraitTest extends TestCase
{

    /**
     * @var ConfigClass
     */
    protected $instance;

    public function setUp()
    {
        $this->instance = new ConfigClass([
            'foo' => 'hello',
            'bar' => 42,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function testConstructor()
    {
        $config = $this->instance->getConfig();

        $this->assertEquals('hello', $config['foo']);
        $this->assertEquals(42, $config['bar']);
    }
}

class ConfigClass
{

    use ConfigTrait;

    public $foo;

    public $bar;

    protected function configure(): array
    {
        return [
            'foo' => 'hi',
            'bar' => 24,
        ];
    }
}
