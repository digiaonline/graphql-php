<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\KnownTypeNamesRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\unknownType;

class KnownTypeNamesRuleTest extends RuleTestCase
{

    public function testKnownTypeNamesAreValid()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Foo($var: String, $required: [String!]!) {
              user(id: 4) {
                pets { ... on Pet { name }, ...PetFields, ... { name } }
              }
            }
            fragment PetFields on Pet {
              name
            }
            ')
        );
    }

    public function testUnknownTypeNamesAreInvalid()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            query Foo($var: JumbledUpLetters) {
              user(id: 4) {
                name
                pets { ... on Badger { name }, ...PetFields }
              }
            }
            fragment PetFields on Peettt {
              name
            }
            '),
            [
                unknownType('JumbledUpLetters', [], [1, 17]),
                unknownType('Badger', [], [4, 19]),
                unknownType('Peettt', ['Pet'], [7, 23]),
            ]
        );
    }

    public function testIgnoresTypeDefinitions()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            type NotInTheSchema {
              field: FooBar
            }
            interface FooBar {
              field: NotInTheSchema
            }
            union U = A | B
            input Blob {
              field: UnknownType
            }
            query Foo($var: NotInTheSchema) {
              user(id: $var) {
                id
              }
            }
            '),
            [unknownType('NotInTheSchema', [], [11, 17])]
        );
    }

    protected function getRuleClassName(): string
    {
        return KnownTypeNamesRule::class;
    }
}
