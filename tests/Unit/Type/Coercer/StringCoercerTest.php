<?php

namespace Digia\GraphQL\Test\Unit\Type\Coercer;

use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Coercer\StringCoercer;

class StringCoercerTest extends TestCase
{
    /**
     * @var StringCoercer
     */
    private $coercer;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->coercer = new StringCoercer();
    }

    /**
     * @throws \Digia\GraphQL\Error\InvalidTypeException
     */
    public function testSuccessfulCoercion(): void
    {
        $this->assertSame('false', $this->coercer->coerce(false));
        $this->assertSame('true', $this->coercer->coerce(true));
        $this->assertSame('null', $this->coercer->coerce(null));
        $this->assertSame('2', $this->coercer->coerce(2));
        $this->assertSame('3.1415926535898', $this->coercer->coerce(pi()));
    }
}