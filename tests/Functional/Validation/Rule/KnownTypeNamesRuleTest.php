<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\KnownTypeNamesRule;
use function Digia\GraphQL\Test\Functional\Validation\unknownType;

class KnownTypeNamesRuleTest extends RuleTestCase
{
    public function testKnownTypeNamesAreValid()
    {
        $this->expectPassesRule(
            new KnownTypeNamesRule(),
            '
query Foo($var: String, $required: [String!]!) {
  user(id: 4) {
    pets { ... on Pet { name }, ...PetFields, ... { name } }
  }
}
fragment PetFields on Pet {
  name
}
'
        );
    }

    public function testUnknownTypeNamesAreInvalid()
    {
        $this->expectFailsRule(
            new KnownTypeNamesRule(),
            '
query Foo($var: JumbledUpLetters) {
  user(id: 4) {
    name
    pets { ... on Badger { name }, ...PetFields }
  }
}
fragment PetFields on Peettt {
  name
}
',
            [
                unknownType('JumbledUpLetters', [], [2, 17]),
                unknownType('Badger', [], [5, 19]),
                unknownType('Peettt', ['Pet'], [8, 23]),
            ]
        );
    }

    public function testIgnoresTypeDefinitions()
    {
        $this->expectFailsRule(
            new KnownTypeNamesRule(),
            '
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
',
            [unknownType('NotInTheSchema', [], [12, 17])]
        );
    }
}
