<?php

namespace Digia\GraphQL\Test\Unit\Type\Coercer;

use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Coercer\IntCoercer;

class IntCoercerTest extends TestCase
{

    /**
     * @var IntCoercer
     */
    private $coercer;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->coercer = new IntCoercer();
    }

    /**
     * @throws \Digia\GraphQL\Error\InvalidTypeException
     */
    public function testSuccessfulCoercion(): void
    {
        $this->assertSame(0, $this->coercer->coerce(false));
        $this->assertSame(1, $this->coercer->coerce(true));
        $this->assertSame(2, $this->coercer->coerce(2.0));
    }

    /**
     * @param mixed $value
     * @dataProvider coerceTooLargeIntegerDataProvider
     * @throws InvalidTypeException
     */
    public function testCoerceTooLargeInteger($value): void
    {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessageRegExp('*Int cannot represent non 32-bit signed integer value*');

        $this->coercer->coerce($value);
    }

    /**
     * @return array
     */
    public function coerceTooLargeIntegerDataProvider(): array
    {
        return [
            ['1273898127398213987219837198273232314324324324324324324324324324'],
            [1e100],
            [-1e100],
        ];
    }

    /**
     * @throws InvalidTypeException
     */
    public function testCoerceFloat(): void
    {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessageRegExp('*Int cannot represent non-integer value*');

        $this->coercer->coerce(4.55);
    }
}
