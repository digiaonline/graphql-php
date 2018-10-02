<?php

namespace Digia\GraphQL\Test\Unit\Error;

use Digia\GraphQL\Error\GraphQLException;
use Digia\GraphQL\Language\Source;
use Digia\GraphQL\Test\TestCase;

class GraphQLExceptionTest extends TestCase
{
    public function testHasLocations()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $exception = new GraphQLException(
            'This is an exception.',
            null,
            new Source('qeury { hello }'),
            [0, 5],
            ['some', 'path']
        );

        $this->assertTrue($exception->hasLocations());

        /** @noinspection PhpUnhandledExceptionInspection */
        $exception = new GraphQLException(
            'This is an exception.',
            null,
            null,
            null,
            ['some', 'path']
        );

        $this->assertFalse($exception->hasLocations());
    }

    public function testToArray()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $exception = new GraphQLException(
            'This is an exception.',
            null,
            new Source('qeury { hello }'),
            [0, 5],
            ['some', 'path']
        );

        $this->assertEquals([
            'message'   => 'This is an exception.',
            'locations' => [
                ['line' => 1, 'column' => 1],
                ['line' => 1, 'column' => 6],
            ],
            'path'      => ['some', 'path'],
        ], $exception->toArray());
    }

    public function testToArrayWithExtensions()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $exception = new GraphQLException(
            'This is an exception.',
            null,
            new Source('qeury { hello }'),
            [0, 5],
            ['some', 'path'],
            ['code' => 'SOME_ERROR_CODE']
        );

        $this->assertEquals([
            'message'    => 'This is an exception.',
            'locations'  => [
                ['line' => 1, 'column' => 1],
                ['line' => 1, 'column' => 6],
            ],
            'path'       => ['some', 'path'],
            'extensions' => ['code' => 'SOME_ERROR_CODE'],
        ], $exception->toArray());
    }
}
