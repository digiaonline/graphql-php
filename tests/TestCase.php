<?php

namespace Digia\GraphQL\Test;

use Digia\GraphQL\Schema\Schema;
use PHPUnit\Framework\TestCase as BaseTestCase;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\parse;

class TestCase extends BaseTestCase
{
    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @param Schema      $schema
     * @param string      $query
     * @param array       $expected
     * @param mixed       $rootValue
     * @param mixed       $contextValue
     * @param array       $variableValues
     * @param string|null $operationName
     */
    protected function assertQueryResultWithSchema(
        Schema $schema,
        string $query,
        array $expected,
        $rootValue = null,
        $contextValue = null,
        array $variableValues = [],
        ?string $operationName = null
    ) {
        /** @noinspection PhpUnhandledExceptionInspection */
        $result = execute($schema, parse(dedent($query)), $rootValue, $contextValue, $variableValues, $operationName);

        $this->assertEquals($expected, $result->toArray());
    }
}
