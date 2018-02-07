<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Language\AST\Node\FieldDefinitionNode;
use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\IDType;
use Digia\GraphQL\Type\Definition\StringType;

class FieldTest extends TestCase
{

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Field
     */
    protected $field;

    public function setUp()
    {
        $this->config = [
            'name' => 'greet',
            'description' => 'Returns a greeting with the given ID.',
            'type' => new StringType(),
            'deprecationReason' => 'Legacy field',
            'arguments' => [
                [
                    'name' => 'ID',
                    'type' => new IDType(),
                ],
            ],
            'astNode' => new FieldDefinitionNode(),
        ];

        $this->field = new Field($this->config);
    }

    /**
     * @throws \Exception
     */
    public function testConfig()
    {
        $this->assertEquals($this->config['name'], $this->field->getName());
        $this->assertEquals($this->config['description'], $this->field->getDescription());
        $this->assertEquals($this->config['deprecationReason'], $this->field->getDeprecationReason());
        $this->assertTrue(is_array($this->field->getArguments()));
        $this->assertInstanceOf(StringType::class, $this->field->getType());
        $this->assertInstanceOf(FieldDefinitionNode::class, $this->field->getAstNode());
    }
}
