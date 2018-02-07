<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Language\AST\Node\EnumValueDefinitionNode;
use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Type\Definition\EnumValue;

class EnumValueTest extends TestCase
{

    /**
     * @var array
     */
    protected $config;

    /**
     * @var EnumValue
     */
    protected $value;

    public function setUp()
    {
        $this->config = [
            'name' => 'RED',
            'description' => 'The color red.',
            'value' => 0,
            'astNode' => new EnumValueDefinitionNode(),
        ];

        $this->value = new EnumValue($this->config);
    }

    /**
     * @throws \Exception
     */
    public function testConfig()
    {
        $this->assertEquals($this->config['name'], $this->value->getName());
        $this->assertEquals($this->config['description'], $this->value->getDescription());
        $this->assertEquals($this->config['value'], $this->value->getValue());
        $this->assertInstanceOf(EnumValueDefinitionNode::class, $this->value->getAstNode());
    }
}
