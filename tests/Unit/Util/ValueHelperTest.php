<?php

namespace Digia\GraphQL\Test\Unit\Util;

use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\NameNode;
use Digia\GraphQL\Language\Node\StringValueNode;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Util\ValueHelper;

/**
 * Class ValueHelperTest
 * @package Digia\GraphQL\Test\Unit\Util
 */
class ValueHelperTest extends TestCase
{
    /**
     * @var ValueHelper
     */
    private $valueHelper;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->valueHelper = new ValueHelper();
    }

    /**
     *
     */
    public function testCompareArguments(): void
    {
        // Test argument count mismatch
        $a = [
            $this->makeStringArgumentNode('name', 'value'),
            $this->makeStringArgumentNode('name', 'value'),
        ];

        $b = [
            $this->makeStringArgumentNode('name', 'value'),
        ];

        $this->assertFalse($this->valueHelper->compareArguments($a, $b));

        // Test with no matching name value
        $a = [
            $this->makeStringArgumentNode('name', 'value'),
        ];

        $b = [
            $this->makeStringArgumentNode('other name', 'value'),
        ];

        $this->assertFalse($this->valueHelper->compareArguments($a, $b));

        // Test with matching name value but mismatching value
        $a = [
            $this->makeStringArgumentNode('name', 'value'),
        ];

        $b = [
            $this->makeStringArgumentNode('name', 'other value'),
        ];

        $this->assertFalse($this->valueHelper->compareArguments($a, $b));

        // Test with full match
        $a = [
            $this->makeStringArgumentNode('name', 'value'),
        ];

        $b = [
            $this->makeStringArgumentNode('name', 'value'),
        ];

        $this->assertTrue($this->valueHelper->compareArguments($a, $b));
    }

    public function testCompareValues(): void
    {
        $a = new StringValueNode('foo', false, null);
        $b = new StringValueNode('bar', false, null);
        $c = new StringValueNode('foo', false, null);

        $this->assertTrue($this->valueHelper->compareValues($a, $c));
        $this->assertFalse($this->valueHelper->compareValues($a, $b));
    }

    /**
     * @param string $name
     * @param string $value
     * @return ArgumentNode
     */
    private function makeStringArgumentNode(string $name, string $value): ArgumentNode
    {
        return new ArgumentNode(new NameNode($name, null), new StringValueNode($value, false, null), null);
    }
}
