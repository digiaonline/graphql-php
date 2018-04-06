<?php

namespace Digia\GraphQL\Test;

use function Digia\GraphQL\graphql;
use function Digia\GraphQL\Language\locationShorthandToArray;
use function Digia\GraphQL\Test\Functional\starWarsSchema;

class QueryTest extends TestCase
{

    // Star Wars Query Tests

    // Basic Queries

    // Correctly identifies R2-D2 as the hero of the Star Wars Saga

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

        $this->assertEquals([
            'data' => [
                'hero' => [
                    'name' => 'R2-D2',
                ],
            ],
        ], $result->toArray());
    }

    // Skip: "Accepts an object with named properties to graphql()"

    // Allows us to query for the ID and friends of R2-D2

    public function testAllowsUsToQueryForTheIDAndFieldsOfR2D2()
    {
        $query = '
        query HeroNameQuery {
          hero {
            id
            name
            friends {
              name
            }
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                'hero' => [
                    'id' => '2001',
                    'name' => 'R2-D2',
                    'friends' => [
                        ['name' => 'Luke Skywalker'],
                        ['name' => 'Han Solo'],
                        ['name' => 'Leia Organa'],
                    ],
                ],
            ],
        ], $result->toArray());
    }

    // Nested Queries

    // Allows us to query for the friends of friends of R2-D2

    public function testAllowsUsToQueryForTheFriendsOfFriendsOfR2D2()
    {
        $query = '
        query HeroNameQuery {
          hero {
            name
            friends {
              name
              appearsIn
              friends {
                name
              }
            }
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                'hero' => [
                    'name' => 'R2-D2',
                    'friends' => [
                        [
                            'name' => 'Luke Skywalker',
                            'appearsIn' => ['NEWHOPE', 'EMPIRE', 'JEDI'],
                            'friends' => [
                                ['name' => 'Han Solo'],
                                ['name' => 'Leia Organa'],
                                ['name' => 'C-3PO'],
                                ['name' => 'R2-D2'],
                            ],
                        ],
                        [
                            'name' => 'Han Solo',
                            'appearsIn' => ['NEWHOPE', 'EMPIRE', 'JEDI'],
                            'friends' => [
                                ['name' => 'Luke Skywalker'],
                                ['name' => 'Leia Organa'],
                                ['name' => 'R2-D2'],
                            ],
                        ],
                        [
                            'name' => 'Leia Organa',
                            'appearsIn' => ['NEWHOPE', 'EMPIRE', 'JEDI'],
                            'friends' => [
                                ['name' => 'Luke Skywalker'],
                                ['name' => 'Han Solo'],
                                ['name' => 'C-3PO'],
                                ['name' => 'R2-D2'],
                            ],
                        ],
                    ],
                ],
            ],
        ], $result->toArray());
    }

    // Using IDs and query parameters to refetch objects

    // Allows us to query for Luke Skywalker directly, using his ID

    public function testAllowsUsToQueryForLukeSkywalkerDirectlyUsingHisID()
    {
        $query = '
        query FetchLukeQuery {
          human(id: "1000") {
            name
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                'human' => [
                    'name' => 'Luke Skywalker',
                ],
            ],
        ], $result->toArray());
    }

    // Allows us to create a generic query, then use it to fetch Luke Skywalker using his ID

    public function testAllowsUsToCreateAGenericQueryThenUseItToFetchLukeSkywalkerUsingHisID(
    )
    {
        $query = '
        query FetchSomeIDQuery($someId: String!) {
          human(id: $someId) {
            name
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query, null, null,
            ['someId' => '1000']);

        $this->assertEquals([
            'data' => [
                'human' => [
                    'name' => 'Luke Skywalker',
                ],
            ],
        ], $result->toArray());
    }

    // Allows us to create a generic query, then use it to fetch Han Solo using his ID

    public function testAllowsUsToCreateAGenericQueryThenUseItToFetchHanSoloUsingHisID(
    )
    {
        $query = '
        query FetchSomeIDQuery($someId: String!) {
          human(id: $someId) {
            name
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query, null, null,
            ['someId' => '1002']);

        $this->assertEquals([
            'data' => [
                'human' => [
                    'name' => 'Han Solo',
                ],
            ],
        ], $result->toArray());
    }

    // Allows us to create a generic query, then pass an invalid ID to get null back

    public function testAllowsUsToCreateAGenericQueryThenPassAnInvalidIdToGetBackNull(
    )
    {
        $query = '
        query FetchSomeIDQuery($someId: String!) {
          human(id: $someId) {
            name
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query, null, null,
            ['someId' => 'not a valid id']);

        $this->assertEquals([
            'data' => [
                'human' => null,
            ],
        ], $result->toArray());
    }

    // Using aliases to change the key in the response

    // Allows us to query for Luke, changing his key with an alias

    public function testAllowsUsToQueryForLukeChangingHisKeyWithAnAlias()
    {
        $query = '
        query FetchLukeAliased {
          luke: human(id: "1000") {
            name
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                'luke' => [
                    'name' => 'Luke Skywalker',
                ],
            ],
        ], $result->toArray());
    }

    // Allows us to query for both Luke and Leia, using two root fields and an alias

    public function testAllowsUsToQueryForBothLukeAndLeiaUsingTwoRootFieldsAndAnAlias(
    )
    {
        $query = '
        query FetchLukeAndLeiaAliased {
          luke: human(id: "1000") {
            name
          }
          leia: human(id: "1003") {
            name
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                'luke' => [
                    'name' => 'Luke Skywalker',
                ],
                'leia' => [
                    'name' => 'Leia Organa',
                ],
            ],
        ], $result->toArray());
    }

    // Uses fragments to express more complex queries

    // Allows us to query using duplicated content

    public function testAllowsUsToQueryUsingDuplicatedContent()
    {
        $query = '
        query FetchLukeAndLeiaAliased {
          luke: human(id: "1000") {
            name
            homePlanet
          }
          leia: human(id: "1003") {
            name
            homePlanet
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                'luke' => [
                    'name' => 'Luke Skywalker',
                    'homePlanet' => 'Tatooine',
                ],
                'leia' => [
                    'name' => 'Leia Organa',
                    'homePlanet' => 'Alderaan',
                ],
            ],
        ], $result->toArray());
    }

    // Allows us to use a fragment to avoid duplicating content

    public function testAllowsUstoUseAFragmentToAvoidDuplicatingContent()
    {
        $query = '
        query FetchLukeAndLeiaAliased {
          luke: human(id: "1000") {
            ...HumanFragment
          }
          leia: human(id: "1003") {
            ...HumanFragment
          }
        }
        
        fragment HumanFragment on Human {
          name
          homePlanet
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                'luke' => [
                    'name' => 'Luke Skywalker',
                    'homePlanet' => 'Tatooine',
                ],
                'leia' => [
                    'name' => 'Leia Organa',
                    'homePlanet' => 'Alderaan',
                ],
            ],
        ], $result->toArray());
    }

    // Using __typename to find the type of an object

    // Allows us to verify that R2-D2 is a droid

    public function testAllowsUsToVerifyThatR2D2IsADroid()
    {
        $query = '
        query CheckTypeOfR2 {
          hero {
            __typename
            name
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                'hero' => [
                    '__typename' => 'Droid',
                    'name' => 'R2-D2',
                ],
            ],
        ], $result->toArray());
    }

    // Allows us to verify that Luke is a human

    public function testAllowsUsToVerifyThatLukeIsAHuman()
    {
        $query = '
        query CheckTypeOfLuke {
          hero(episode: EMPIRE) {
            __typename
            name
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                'hero' => [
                    '__typename' => 'Human',
                    'name' => 'Luke Skywalker',
                ],
            ],
        ], $result->toArray());
    }

    // Reporting errors raised in resolvers

    // Correctly reports error on accessing secretBackstory

    public function testCorrectlyReportsErrorOnAccessingSecretBackstory()
    {
        $query = '
        query HeroNameQuery {
          hero {
            name
            secretBackstory
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                'hero' => [
                    'name' => 'R2-D2',
                    'secretBackstory' => null,
                ],
            ],
            'errors' => [
                [
                    'message' => 'secretBackstory is secret.',
                    'locations' => [locationShorthandToArray([5, 13])],
                    'path' => ['hero', 'secretBackstory'],
                ],
            ],
        ], $result->toArray());
    }

    // Correctly reports error on accessing secretBackstory in a list

    public function testCorrectlyReportsErrorOnAccessingSecretBackstoryInAList()
    {
        $query = '
        query HeroNameQuery {
          hero {
            name
            friends {
              name
              secretBackstory
            }
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                'hero' => [
                    'name' => 'R2-D2',
                    'friends' => [
                        [
                            'name' => 'Luke Skywalker',
                            'secretBackstory' => null,
                        ],
                        [
                            'name' => 'Han Solo',
                            'secretBackstory' => null,
                        ],
                        [
                            'name' => 'Leia Organa',
                            'secretBackstory' => null,
                        ],
                    ],
                ],
            ],
            'errors' => [
                [
                    'message' => 'secretBackstory is secret.',
                    'locations' => [locationShorthandToArray([7, 15])],
                    'path' => ['hero', 'friends', 0, 'secretBackstory'],
                ],
                [
                    'message' => 'secretBackstory is secret.',
                    'locations' => [locationShorthandToArray([7, 15])],
                    'path' => ['hero', 'friends', 1, 'secretBackstory'],
                ],
                [
                    'message' => 'secretBackstory is secret.',
                    'locations' => [locationShorthandToArray([7, 15])],
                    'path' => ['hero', 'friends', 2, 'secretBackstory'],
                ],
            ],
        ], $result->toArray());
    }

    // Correctly reports error on accessing through an alias

    public function testCorrectlyReportsErrorOnAccessingThroughAnAlias()
    {
        $query = '
        query HeroNameQuery {
          mainHero: hero {
            name
            story: secretBackstory
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                'mainHero' => [
                    'name' => 'R2-D2',
                    'story' => null,
                ],
            ],
            'errors' => [
                [
                    'message' => 'secretBackstory is secret.',
                    'locations' => [locationShorthandToArray([5, 13])],
                    'path' => ['mainHero', 'story'],
                ],
            ],
        ], $result->toArray());
    }
}
