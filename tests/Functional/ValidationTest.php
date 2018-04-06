<?php

namespace Digia\GraphQL\Test\Functional;

use Digia\GraphQL\Language\Source;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\validate;

class ValidationTest extends TestCase
{

    // Star Wars Validation Tests

    // Basic Queries

    // Validates a complex but valid query

    public function testValidatesAComplexButValidQuery()
    {
        $query = '
        query NestedQueryWithFragment {
          hero {
            ...NameAndAppearances
            friends {
              ...NameAndAppearances
              friends {
                ...NameAndAppearances
              }
            }
          }
        }
        
        fragment NameAndAppearances on Character {
          name
          appearsIn
        }
        ';

        $this->assertEmpty($this->validateQuery($query));
    }

    // Notes that non-existent fields are invalid

    protected function validateQuery($query)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $source = new Source($query, 'StarWars.graphql');

        /** @noinspection PhpUnhandledExceptionInspection */
        return validate(starWarsSchema(), parse($source));
    }

    // Requires fields on objects

    public function testNodesThatNonExistentFieldsAreInvalid()
    {
        $query = '
        query HeroSpaceshipQuery {
          hero {
            favoriteSpaceship
          }
        }
        ';

        $this->assertNotEmpty($this->validateQuery($query));
    }

    // Disallows fields on scalars

    public function testRequiresFieldsOnObjects()
    {
        $query = '
        query HeroNoFieldsQuery {
          hero
        }
        ';

        $this->assertNotEmpty($this->validateQuery($query));
    }

    // Disallows object fields on interfaces

    public function testDisallowsFieldsOnScalars()
    {
        $query = '
        query HeroNoFieldsQuery {
          hero {
            name {
              firstCharacterOfName
            }
          }
        }
        ';

        $this->assertNotEmpty($this->validateQuery($query));
    }

    // Allows object fields in fragments

    public function testDisallowsObjectFieldsOnInterfaces()
    {
        $query = '
        query DroidFieldOnCharacter {
          hero {
            name
            primaryFunction
          }
        }
        ';

        $this->assertNotEmpty($this->validateQuery($query));
    }

    // Allows object fields in inline fragments

    public function testAllowsObjectFieldsInFragments()
    {
        $query = '
        query DroidFieldInFragment {
          hero {
            name
            ...DroidFields
          }
        }
        
        fragment DroidFields on Droid {
          primaryFunction
        }
        ';

        $this->assertEmpty($this->validateQuery($query));
    }

    public function testAllowsObjectFieldsInInlineFragments()
    {
        $query = '
        query DroidFieldInFragment {
          hero {
            name
            ... on Droid {
              primaryFunction
            }
          }
        }
        ';

        $this->assertEmpty($this->validateQuery($query));
    }
}
