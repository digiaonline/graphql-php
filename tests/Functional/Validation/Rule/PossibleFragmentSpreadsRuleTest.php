<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\PossibleFragmentSpreadsRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\typeIncompatibleAnonymousSpread;
use function Digia\GraphQL\Test\Functional\Validation\typeIncompatibleSpread;

class PossibleFragmentSpreadsRuleTest extends RuleTestCase
{
    protected function getRuleClassName(): string
    {
        return PossibleFragmentSpreadsRule::class;
    }

    public function testOfTheSameObject()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment objectWithinObject on Dog { ...dogFragment }
            fragment dogFragment on Dog { barkVolume }
            ')
        );
    }

    public function testOfTheSameObjectWithInlineFragment()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment objectWithinObjectAnon on Dog { ... on Dog { barkVolume } }
            ')
        );
    }

    public function testObjectIntoAnImplementedInterface()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment objectWithinInterface on Pet { ...dogFragment }
            fragment dogFragment on Dog { barkVolume }
            ')
        );
    }

    public function testObjectIntoContainingUnion()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment objectWithinUnion on CatOrDog { ...dogFragment }
            fragment dogFragment on Dog { barkVolume }
            ')
        );
    }

    public function testUnionIntoContainedObject()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment unionWithinObject on Dog { ...catOrDogFragment }
            fragment catOrDogFragment on CatOrDog { __typename }
            ')
        );
    }

    public function testUnionIntoOverlappingInterface()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment unionWithinInterface on Pet { ...catOrDogFragment }
            fragment catOrDogFragment on CatOrDog { __typename }
            ')
        );
    }

    public function testUnionIntoOverlappingUnion()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment unionWithinUnion on DogOrHuman { ...catOrDogFragment }
            fragment catOrDogFragment on CatOrDog { __typename }
            ')
        );
    }

    public function testInterfaceIntoImplementedObject()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment interfaceWithinObject on Dog { ...petFragment }
            fragment petFragment on Pet { name }
            ')
        );
    }

    public function testInterfaceIntoOverlappingInterface()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment interfaceWithinInterface on Pet { ...beingFragment }
            fragment beingFragment on Being { name }
            ')
        );
    }

    public function testInterfaceIntoOverlappingInterfaceInInlineFragment()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment interfaceWithinInterface on Pet { ... on Being { name } }
            ')
        );
    }

    public function testInterfaceIntoOverlappingUnion()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment interfaceWithinUnion on CatOrDog { ...petFragment }
            fragment petFragment on Pet { name }
            ')
        );
    }

    public function testIgnoresIncorrectType()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment petFragment on Pet { ...badInADifferentWay }
            fragment badInADifferentWay on String { name }
            ')
        );
    }

    public function testDifferentObjectIntoObject()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment invalidObjectWithinObject on Cat { ...dogFragment }
            fragment dogFragment on Dog { barkVolume }
            '),
            [typeIncompatibleSpread('dogFragment', 'Cat', 'Dog', [1, 45])]
        );
    }

    public function testDifferentObjectIntoObjectInInlineFragment()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment invalidObjectWithinObjectAnon on Cat {
              ... on Dog { barkVolume }
            }
            '),
            [typeIncompatibleAnonymousSpread('Cat', 'Dog', [2, 3])]
        );
    }

    public function testObjectIntoNotImplementingInterface()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment invalidObjectWithinInterface on Pet { ...humanFragment }
            fragment humanFragment on Human { pets { name } }
            '),
            [typeIncompatibleSpread('humanFragment', 'Pet', 'Human', [1, 48])]
        );
    }

    public function testObjectIntoNotContainingUnion()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment invalidObjectWithinUnion on CatOrDog { ...humanFragment }
            fragment humanFragment on Human { pets { name } }
            '),
            [typeIncompatibleSpread('humanFragment', 'CatOrDog', 'Human', [1, 49])]
        );
    }

    public function testUnionIntoNotContainedObject()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment invalidUnionWithinObject on Human { ...catOrDogFragment }
            fragment catOrDogFragment on CatOrDog { __typename }
            '),
            [typeIncompatibleSpread('catOrDogFragment', 'Human', 'CatOrDog', [1, 46])]
        );
    }

    public function testUnionIntoNonOverlappingInterface()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment invalidUnionWithinInterface on Pet { ...humanOrAlienFragment }
            fragment humanOrAlienFragment on HumanOrAlien { __typename }
            '),
            [typeIncompatibleSpread('humanOrAlienFragment', 'Pet', 'HumanOrAlien', [1, 47])]
        );
    }

    public function testUnionIntoNonOverlappingUnion()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment invalidUnionWithinUnion on CatOrDog { ...humanOrAlienFragment }
            fragment humanOrAlienFragment on HumanOrAlien { __typename }
            '),
            [typeIncompatibleSpread('humanOrAlienFragment', 'CatOrDog', 'HumanOrAlien', [1, 48])]
        );
    }

    public function testInterfaceIntoNonImplementingObject()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment invalidInterfaceWithinObject on Cat { ...intelligentFragment }
            fragment intelligentFragment on Intelligent { iq }
            '),
            [typeIncompatibleSpread('intelligentFragment', 'Cat', 'Intelligent', [1, 48])]
        );
    }

    public function testInterfaceIntoNonOverlappingInterface()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment invalidInterfaceWithinInterface on Pet {
              ...intelligentFragment
            }
            fragment intelligentFragment on Intelligent { iq }
            '),
            [typeIncompatibleSpread('intelligentFragment', 'Pet', 'Intelligent', [2, 3])]
        );
    }

    public function testInterfaceIntoNonOverlappingInterfaceInInlineFragment()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment invalidInterfaceWithinInterfaceAnon on Pet {
              ...on Intelligent { iq }
            }
            '),
            [typeIncompatibleAnonymousSpread('Pet', 'Intelligent', [2, 3])]
        );
    }

    public function testInterfaceIntoNonOverlappingUnion()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment invalidInterfaceWithinUnion on HumanOrAlien { ...petFragment }
            fragment petFragment on Pet { name }
            '),
            [typeIncompatibleSpread('petFragment', 'HumanOrAlien', 'Pet', [1, 56])]
        );
    }
}
