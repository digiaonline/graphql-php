<?php

namespace Digia\GraphQL\Test\Unit\Util;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\NodeBuilderInterface;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Util\NodeComparator;

class NodeComparatorTest extends TestCase
{
    /**
     * @var NodeBuilderInterface
     */
    private $nodeBuilder;

    public function setUp()
    {
        $this->nodeBuilder = GraphQL::make(NodeBuilderInterface::class);
    }

    public function testCompareSameNode()
    {
        $node = $this->nodeBuilder->build([
            'kind'  => NodeKindEnum::NAME,
            'value' => ['kind' => NodeKindEnum::STRING, 'value' => 'Foo'],
        ]);

        $this->assertTrue(NodeComparator::compare($node, $node));
    }

    public function testCompareDifferentNode()
    {
        $node = $this->nodeBuilder->build([
            'kind'  => NodeKindEnum::NAME,
            'value' => ['kind' => NodeKindEnum::STRING, 'value' => 'Foo'],
        ]);

        $other = $this->nodeBuilder->build([
            'kind'  => NodeKindEnum::NAME,
            'value' => ['kind' => NodeKindEnum::STRING, 'value' => 'Bar'],
        ]);

        $this->assertFalse(NodeComparator::compare($node, $other));
    }
}
