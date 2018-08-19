<?php

namespace Digia\GraphQL\Test\Unit\Util;

use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Util\NameHelper;

/**
 * Class NameHelperTest
 * @package Digia\GraphQL\Test\Unit\Util
 */
class NameHelperTest extends TestCase
{

    /**
     * @expectedException  \Digia\GraphQL\Error\ValidationException
     * @expectedExceptionMessage Name "__invalid" must not begin with "__", which is reserved by GraphQL introspection.
     */
    public function testAssertInvalidReservedCharacters()
    {
        $exception = NameHelper::isValidError('__invalid');

        if ($exception !== null) {
            throw $exception;
        }
    }

    /**
     * @expectedException \Digia\GraphQL\Error\ValidationException
     * @expectedExceptionMessage Names must match /^[_a-zA-Z][_a-zA-Z0-9]*$/ but "-" does not.
     */
    public function testAssertInvalidRegularExpression()
    {
        $exception = NameHelper::isValidError('-');

        if ($exception !== null) {
            throw $exception;
        }
    }

    public function testIsValidErrorNoError()
    {
        $exception = NameHelper::isValidError('name');

        $this->assertNull($exception);
    }
}
