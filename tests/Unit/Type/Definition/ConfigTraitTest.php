<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Type\Definition\ConfigTrait;

class ConfigTraitTest extends TestCase
{

    /**
     * @var Dummy
     */
    protected $dummy;

    public function setUp()
    {
        $this->dummy = new Dummy([
            'foo' => 'hello',
            'bar' => 42,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function testConstructor()
    {
        $config = $this->dummy->getConfig();

        $this->assertEquals('hello', $config['foo']);
        $this->assertEquals(42, $config['bar']);
    }
}

class Dummy
{

    public $foo;

    public $bar;

    use ConfigTrait;

    protected function configure(): array
    {
        return [
            'foo' => 'hi',
            'bar' => 24,
        ];
    }
}
