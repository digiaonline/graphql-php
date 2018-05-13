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
        $nameHelper = new NameHelper();

        $nameHelper->assertInvalid('__invalid');
    }

    /**
     * @expectedException \Digia\GraphQL\Error\ValidationException
     * @expectedExceptionMessage Names must match /^[_a-zA-Z][_a-zA-Z0-9]*$/ but "-" does not.
     */
    public function testAssertInvalidRegularExpression()
    {
        $nameHelper = new NameHelper();

        $nameHelper->assertInvalid('-');
    }

    public function testIsValidErrorNoError()
    {
        $nameHelper = new NameHelper();

        $this->assertNull($nameHelper->isValidError('name'));
    }
}
