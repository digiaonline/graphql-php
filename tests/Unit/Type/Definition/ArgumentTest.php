<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Language\AST\Node\InputObjectTypeDefinitionNode;
use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Type\Definition\Argument;
use Digia\GraphQL\Type\Definition\IntType;

class ArgumentTest extends TestCase
{

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Argument
     */
    protected $argument;

    protected function setUp()
    {
        $this->config = [
            'name'         => 'Age',
            'description'  => 'Person age.',
            'type'         => new IntType(),
            'defaultValue' => 0,
            'astNode'      => new InputObjectTypeDefinitionNode(),
        ];

        $this->argument = new Argument($this->config);
    }

    /**
     * @throws \Exception
     */
    public function testConfig()
    {
        $this->assertEquals($this->config['name'], $this->argument->getName());
        $this->assertEquals($this->config['description'], $this->argument->getDescription());
        $this->assertEquals($this->config['defaultValue'], $this->argument->getDefaultValue());
        $this->assertInstanceOf(IntType::class, $this->argument->getType());
        $this->assertInstanceOf(InputObjectTypeDefinitionNode::class, $this->argument->getAstNode());
    }
}
