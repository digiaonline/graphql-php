<?php

namespace Digia\GraphQL\Test;

use function Digia\GraphQL\graphql;
use function Digia\GraphQL\Test\Functional\starWarsSchema;

class QueryTest extends TestCase
{
    public function testCorrectlyIdentifiesR2D2AsTheHeroOfTheStarWarsSaga()
    {
        $query = '
        query HeroNameQuery {
          hero {
            name
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->markTestIncomplete('INCOMPLETE: We cannot yet resolve data for interface types.');

        $this->assertEquals([
            'data' => [
                'hero' => [
                    'name' => 'R2-D2',
                ],
            ],
        ], $result->toArray());
    }
}
