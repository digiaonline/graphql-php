<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition\Behavior;

use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Behavior\ConfigTrait;
use Digia\GraphQL\Type\Definition\Behavior\DescriptionTrait;

class DescriptionTraitTest extends TestCase
{

    /**
     * @var DescribedClass
     */
    protected $instance;

    public function setUp()
    {
        $this->instance = new DescribedClass([
            'description' => 'Some description',
        ]);
    }

    /**
     * @throws \Exception
     */
    public function testGetDescription()
    {
        $this->assertEquals('Some description', $this->instance->getDescription());
    }
}

class DescribedClass {
    use DescriptionTrait;
    use ConfigTrait;
}
