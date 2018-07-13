<?php

namespace Digia\GraphQL\Test\Functional;

use function Digia\GraphQL\buildSchema;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\graphql;
use function Digia\GraphQL\Type\newScalarType;

class ScalarNameIntrospectionTest extends TestCase
{
    /**
     * This is working as expected - the introspection query returns the name
     * of the Postcode scalar properly, and no errors are thrown
     */
    public function testCanIntrospectNameOnAPostcodeScalar()
    {
        $schema = '
        scalar Postcode
        type Query {
            hello: Postcode
        }
        ';


        $schema = buildSchema($schema);

        $query = '
        query IntrospectionDroidDescriptionQuery {
          __type(name: "Postcode") {
            name
          }
        }
        ';

        $result = graphql($schema, $query);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertEquals([
            'data' => [
                '__type' => [
                    'name'        => 'Postcode',
                ],
            ],
        ], $result);
    }

    /**
     * This is breaking - the name of the Date scalar returns as null, and we
     * receive errors from trying to call date(). The same happens for any scalar
     * that is named the same as a function in the global namespace (e.g. time, fopen, phpinfo)
     */
    public function testCanIntrospectNameOnADateScalar()
    {
        $schema = '
        scalar Date
        type Query {
            hello: Date
        }
        ';


        $schema = buildSchema($schema);

        $query = '
        query IntrospectionDroidDescriptionQuery {
          __type(name: "Date") {
            name
          }
        }
        ';

        $result = graphql($schema, $query);

        $this->assertArrayNotHasKey('errors', $result, json_encode($result['errors']));
        $this->assertEquals([
            'data' => [
                '__type' => [
                    'name'        => 'Date',
                ],
            ],
        ], $result);
    }
}