<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition\Behavior;

use Digia\GraphQL\Language\AST\Node\ObjectTypeExtensionNode;
use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Behavior\ConfigTrait;
use Digia\GraphQL\Type\Definition\Behavior\ExtensionASTNodesTrait;

class ExtensionASTNodesTraitTest extends TestCase
{

    /**
     * @var ExtensionASTNodesClass
     */
    protected $instance;

    public function setUp()
    {
        $this->instance = new ExtensionASTNodesClass([
            'extensionASTNodes' => [
                new ObjectTypeExtensionNode(),
            ],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function testGetExtensionASTNodes()
    {
        $nodes = $this->instance->getExtensionAstNodes();

        $this->assertTrue(is_array($nodes));
        $this->assertEquals(1, count($nodes));
        $this->assertInstanceOf(ObjectTypeExtensionNode::class, array_pop($nodes));
    }
}

class ExtensionASTNodesClass {
    use ExtensionASTNodesTrait;
    use ConfigTrait;
}
