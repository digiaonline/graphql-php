<?php

namespace Digia\GraphQL\Test\Unit\Type\Coercer;

use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Coercer\FloatCoercer;

class FloatCoercerTest extends TestCase
{
    /**
     * @var FloatCoercer
     */
    private $coercer;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->coercer = new FloatCoercer();
    }

    /**
     * @throws \Digia\GraphQL\Error\InvalidTypeException
     */
    public function testSuccessfulCoercion(): void
    {
        $this->assertSame(0.0, $this->coercer->coerce(false));
        $this->assertSame(1.0, $this->coercer->coerce(true));
        $this->assertSame(2.0, $this->coercer->coerce(2));
    }
}