<?php

namespace Digia\GraphQL\Test\Unit\Util;

use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Util\AbstractEnum;

/**
 * Class AbstractEnumTest
 * @package Digia\GraphQL\Test\Unit\Util
 */
class AbstractEnumTest extends TestCase
{

    /**
     * @throws \ReflectionException
     */
    public function testValues()
    {
        $this->assertEquals([
            1,
            2
        ], DummyEnum::values());
    }
}

/**
 * Class DummyEnum
 * @package Digia\GraphQL\Test\Unit\Util
 */
class DummyEnum extends AbstractEnum
{
    public const VALUE_1 = 1;
    public const VALUE_2 = 2;
}
