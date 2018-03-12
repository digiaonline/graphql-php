<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use function Digia\GraphQL\Language\dedent;
use Digia\GraphQL\Validation\Rule\NoFragmentCyclesRule;
use function Digia\GraphQL\Test\Functional\Validation\fragmentCycle;

class NoFragmentCyclesRuleTest extends RuleTestCase
{
    public function testSingleReferenceIsValid()
    {
        $this->expectPassesRule(
            new NoFragmentCyclesRule(),
            dedent('
            fragment fragA on Dog { ...fragB }
            fragment fragB on Dog { name }
            ')
        );
    }

    public function testSpreadingTwiceIsNotCircular()
    {
        $this->expectPassesRule(
            new NoFragmentCyclesRule(),
            dedent('
            fragment fragA on Dog { ...fragB, ...fragB }
            fragment fragB on Dog { name }
            ')
        );
    }

    public function testSpreadingTwiceIndirectlyIsNotCircular()
    {
        $this->expectPassesRule(
            new NoFragmentCyclesRule(),
            dedent('
            fragment fragA on Dog { ...fragB, ...fragC }
            fragment fragB on Dog { ...fragC }
            fragment fragC on Dog { name }
            ')
        );
    }

    public function testDoubleSpreadWithinAbstractTypes()
    {
        $this->expectPassesRule(
            new NoFragmentCyclesRule(),
            dedent('
            fragment nameFragment on Pet {
              ... on Dog { name }
              ... on Cat { name }
            }
            fragment spreadsInAnon on Pet {
              ... on Dog { ...nameFragment }
              ... on Cat { ...nameFragment }
            }
            ')
        );
    }

    public function testDoesNotFalsePositiveOnUnknownFragment()
    {
        $this->expectPassesRule(
            new NoFragmentCyclesRule(),
            dedent('
            fragment nameFragment on Pet {
              ...UnknownFragment
            }
            ')
        );
    }

    public function testSpreadingRecursivelyWithinFieldsFails()
    {
        $this->expectFailsRule(
            new NoFragmentCyclesRule(),
            dedent('
            fragment fragA on Human { relatives { ...fragA } },
            '),
            [fragmentCycle('fragA', [], [[1, 39]])]
        );
    }

    public function testNoSpreadingItselfDirectly()
    {
        $this->expectFailsRule(
            new NoFragmentCyclesRule(),
            dedent('
            fragment fragA on Dog { ...fragA }
            '),
            [fragmentCycle('fragA', [], [[1, 25]])]
        );
    }

    public function testNoSpreadingItselfDirectlyWithinInlineFragment()
    {
        $this->expectFailsRule(
            new NoFragmentCyclesRule(),
            dedent('
            fragment fragA on Pet {
              ... on Dog {
                ...fragA
              }
            }
            '),
            [fragmentCycle('fragA', [], [[3, 5]])]
        );
    }

    public function testNoSpreadingItselfIndirectly()
    {
        $this->expectFailsRule(
            new NoFragmentCyclesRule(),
            dedent('
            fragment fragA on Dog { ...fragB }
            fragment fragB on Dog { ...fragA }
            '),
            [fragmentCycle('fragA', ['fragB'], [[1, 25], [2, 25]])]
        );
    }

    public function testNoSpreadingItselfIndirectlyInOppositeOrder()
    {
        $this->expectFailsRule(
            new NoFragmentCyclesRule(),
            dedent('
            fragment fragB on Dog { ...fragA }
            fragment fragA on Dog { ...fragB }
            '),
            [fragmentCycle('fragB', ['fragA'], [[1, 25], [2, 25]])]
        );
    }

    public function testNoSpreadingItselfIndirectlyWithinInlineFragment()
    {
        $this->expectFailsRule(
            new NoFragmentCyclesRule(),
            dedent('
            fragment fragA on Pet {
              ... on Dog {
                ...fragB
              }
            }
            fragment fragB on Pet {
              ... on Dog {
                ...fragA
              }
            }
            '),
            [fragmentCycle('fragA', ['fragB'], [[3, 5], [8, 5]])]
        );
    }

    public function testNoSpreadingItselfDeeply()
    {
        $this->expectFailsRule(
            new NoFragmentCyclesRule(),
            dedent('
            fragment fragA on Dog { ...fragB }
            fragment fragB on Dog { ...fragC }
            fragment fragC on Dog { ...fragO }
            fragment fragX on Dog { ...fragY }
            fragment fragY on Dog { ...fragZ }
            fragment fragZ on Dog { ...fragO }
            fragment fragO on Dog { ...fragP }
            fragment fragP on Dog { ...fragA, ...fragX }
            '),
            [
                fragmentCycle(
                    'fragA',
                    ['fragB', 'fragC', 'fragO', 'fragP'],
                    [[1, 25], [2, 25], [3, 25], [7, 25], [8, 25]]
                ),
                fragmentCycle(
                    'fragO',
                    ['fragP', 'fragX', 'fragY', 'fragZ'],
                    [[7, 25], [8, 35], [4, 25], [5, 25], [6, 25]]
                )
            ]
        );
    }

    public function testNoSpreadingItselfDeeplyTwoPaths()
    {
        $this->expectFailsRule(
            new NoFragmentCyclesRule(),
            dedent('
            fragment fragA on Dog { ...fragB, ...fragC }
            fragment fragB on Dog { ...fragA }
            fragment fragC on Dog { ...fragA }
            '),
            [
                fragmentCycle('fragA', ['fragB'], [[1, 25], [2, 25]]),
                fragmentCycle('fragA', ['fragC'], [[1, 35], [3, 25]]),
            ]
        );
    }

    public function testNoSpreadingItselfDeeplyTwoPathsOppositeOrder()
    {
        $this->expectFailsRule(
            new NoFragmentCyclesRule(),
            dedent('
            fragment fragA on Dog { ...fragC }
            fragment fragB on Dog { ...fragC }
            fragment fragC on Dog { ...fragA, ...fragB }
            '),
            [
                fragmentCycle('fragA', ['fragC'], [[1, 25], [3, 25]]),
                fragmentCycle('fragC', ['fragB'], [[3, 35], [2, 25]]),
            ]
        );
    }

    public function testNoSpreadingItselfDeeplyAndImmediately()
    {
        $this->expectFailsRule(
            new NoFragmentCyclesRule(),
            dedent('
            fragment fragA on Dog { ...fragB }
            fragment fragB on Dog { ...fragB, ...fragC }
            fragment fragC on Dog { ...fragA, ...fragB }
            '),
            [
                fragmentCycle('fragB', [], [[2, 25]]),
                fragmentCycle('fragA', ['fragB', 'fragC'], [[1, 25], [2, 35], [3, 25]]),
                fragmentCycle('fragB', ['fragC'], [[2, 35], [3, 35]]),
            ]
        );
    }
}
