<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\ExecutableDefinitionsRule;
use function Digia\GraphQL\Language\locationShorthandToArray;
use function Digia\GraphQL\Validation\nonExecutableDefinitionMessage;
use Digia\GraphQL\Validation\Rule\PossibleFragmentSpreadsRule;
use function Digia\GraphQL\Validation\typeIncompatibleAnonymousSpreadMessage;
use function Digia\GraphQL\Validation\typeIncompatibleSpreadMessage;

function typeIncompatibleSpread($fragmentName, $parentType, $fragmentType, $location)
{
    return [
        'message'   => typeIncompatibleSpreadMessage($fragmentName, $parentType, $fragmentType),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function typeIncompatibleAnonymousSpread($parentType, $fragmentType, $location)
{
    return [
        'message'   => typeIncompatibleAnonymousSpreadMessage($parentType, $fragmentType),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

class PossibleFragmentSpreadsRuleTest extends RuleTestCase
{
    public function testOfTheSameObject()
    {
        $this->expectPassesRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment objectWithinObject on Dog { ...dogFragment }
fragment dogFragment on Dog { barkVolume }
'
        );
    }

    public function testOfTheSameObjectWithInlineFragment()
    {
        $this->expectPassesRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment objectWithinObjectAnon on Dog { ... on Dog { barkVolume } }
'
        );
    }

    public function testObjectIntoAnImplementedInterface()
    {
        $this->expectPassesRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment objectWithinInterface on Pet { ...dogFragment }
fragment dogFragment on Dog { barkVolume }
'
        );
    }

    public function testObjectIntoContainingUnion()
    {
        $this->expectPassesRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment objectWithinUnion on CatOrDog { ...dogFragment }
fragment dogFragment on Dog { barkVolume }
'
        );
    }

    public function testUnionIntoContainedObject()
    {
        $this->expectPassesRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment unionWithinObject on Dog { ...catOrDogFragment }
fragment catOrDogFragment on CatOrDog { __typename }
'
        );
    }

    public function testUnionIntoOverlappingInterface()
    {
        $this->expectPassesRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment unionWithinInterface on Pet { ...catOrDogFragment }
fragment catOrDogFragment on CatOrDog { __typename }
'
        );
    }

    public function testUnionIntoOverlappingUnion()
    {
        $this->expectPassesRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment unionWithinUnion on DogOrHuman { ...catOrDogFragment }
fragment catOrDogFragment on CatOrDog { __typename }
'
        );
    }

    public function testInterfaceIntoImplementedObject()
    {
        $this->expectPassesRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment interfaceWithinObject on Dog { ...petFragment }
fragment petFragment on Pet { name }
'
        );
    }

    public function testInterfaceIntoOverlappingInterface()
    {
        $this->expectPassesRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment interfaceWithinInterface on Pet { ...beingFragment }
fragment beingFragment on Being { name }
'
        );
    }

    public function testInterfaceIntoOverlappingInterfaceInInlineFragment()
    {
        $this->expectPassesRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment interfaceWithinInterface on Pet { ... on Being { name } }
'
        );
    }

    public function testInterfaceIntoOverlappingUnion()
    {
        $this->expectPassesRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment interfaceWithinUnion on CatOrDog { ...petFragment }
fragment petFragment on Pet { name }
'
        );
    }

    public function testIgnoresIncorrectType()
    {
        $this->expectPassesRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment petFragment on Pet { ...badInADifferentWay }
fragment badInADifferentWay on String { name }
'
        );
    }

    public function testDifferentObjectIntoObject()
    {
        $this->expectFailsRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment invalidObjectWithinObject on Cat { ...dogFragment }
fragment dogFragment on Dog { barkVolume }
',
            [typeIncompatibleSpread('dogFragment', 'Cat', 'Dog', [2, 45])]
        );
    }

    public function testDifferentObjectIntoObjectInInlineFragment()
    {
        $this->expectFailsRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment invalidObjectWithinObjectAnon on Cat {
  ... on Dog { barkVolume }
}
',
            [typeIncompatibleAnonymousSpread('Cat', 'Dog', [3, 3])]
        );
    }

    public function testObjectIntoNotImplementingInterface()
    {
        $this->expectFailsRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment invalidObjectWithinInterface on Pet { ...humanFragment }
fragment humanFragment on Human { pets { name } }
',
            [typeIncompatibleSpread('humanFragment', 'Pet', 'Human', [2, 48])]
        );
    }

    public function testObjectIntoNotContainingUnion()
    {
        $this->expectFailsRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment invalidObjectWithinUnion on CatOrDog { ...humanFragment }
fragment humanFragment on Human { pets { name } }
',
            [typeIncompatibleSpread('humanFragment', 'CatOrDog', 'Human', [2, 49])]
        );
    }

    public function testUnionIntoNotContainedObject()
    {
        $this->expectFailsRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment invalidUnionWithinObject on Human { ...catOrDogFragment }
fragment catOrDogFragment on CatOrDog { __typename }
',
            [typeIncompatibleSpread('catOrDogFragment', 'Human', 'CatOrDog', [2, 46])]
        );
    }

    public function testUnionIntoNonOverlappingInterface()
    {
        $this->expectFailsRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment invalidUnionWithinInterface on Pet { ...humanOrAlienFragment }
fragment humanOrAlienFragment on HumanOrAlien { __typename }
',
            [typeIncompatibleSpread('humanOrAlienFragment', 'Pet', 'HumanOrAlien', [2, 47])]
        );
    }

    public function testUnionIntoNonOverlappingUnion()
    {
        $this->expectFailsRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment invalidUnionWithinUnion on CatOrDog { ...humanOrAlienFragment }
fragment humanOrAlienFragment on HumanOrAlien { __typename }
',
            [typeIncompatibleSpread('humanOrAlienFragment', 'CatOrDog', 'HumanOrAlien', [2, 48])]
        );
    }

    public function testInterfaceIntoNonImplementingObject()
    {
        $this->expectFailsRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment invalidInterfaceWithinObject on Cat { ...intelligentFragment }
fragment intelligentFragment on Intelligent { iq }
',
            [typeIncompatibleSpread('intelligentFragment', 'Cat', 'Intelligent', [2, 48])]
        );
    }

    public function testInterfaceIntoNonOverlappingInterface()
    {
        $this->expectFailsRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment invalidInterfaceWithinInterface on Pet {
  ...intelligentFragment
}
fragment intelligentFragment on Intelligent { iq }
',
            [typeIncompatibleSpread('intelligentFragment', 'Pet', 'Intelligent', [3, 3])]
        );
    }

    public function testInterfaceIntoNonOverlappingInterfaceInInlineFragment()
    {
        $this->expectFailsRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment invalidInterfaceWithinInterfaceAnon on Pet {
  ...on Intelligent { iq }
}
',
            [typeIncompatibleAnonymousSpread('Pet', 'Intelligent', [3, 3])]
        );
    }

    public function testInterfaceIntoNonOverlappingUnion()
    {
        $this->expectFailsRule(
            new PossibleFragmentSpreadsRule(),
            '
fragment invalidInterfaceWithinUnion on HumanOrAlien { ...petFragment }
fragment petFragment on Pet { name }
',
            [typeIncompatibleSpread('petFragment', 'HumanOrAlien', 'Pet', [2, 56])]
        );
    }
}
