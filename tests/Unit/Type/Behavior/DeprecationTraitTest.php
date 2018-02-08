<?php

namespace Digia\GraphQL\Test\Unit\Type\Behavior;

use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Type\Behavior\DeprecationTrait;

class DeprecationTraitTest extends TestCase
{
    use DeprecationTrait;

    /**
     * @throws \Exception
     */
    public function testIsDeprecated()
    {
        $this->setDeprecationReason('No longer used.');
        $this->assertTrue($this->isDeprecated());
    }

    /**
     * @throws \TypeError
     * @expectedException \TypeError
     */
    public function testSetIsDeprecated()
    {
        $this->setIsDeprecated(true);
    }
}

