<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\UniqueDirectivesPerLocationRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\duplicateDirective;

class UniqueDirectivesPerLocationRuleTest extends RuleTestCase
{
    public function testNoDirectives()
    {
        $this->expectPassesRule(
            new UniqueDirectivesPerLocationRule(),
            dedent('
            fragment Test on Type {
              field
            }
            ')
        );
    }

    public function testUniqueDirectivesInDifferentLocations()
    {
        $this->expectPassesRule(
            new UniqueDirectivesPerLocationRule(),
            dedent('
            fragment Test on Type @directiveA {
              field @directiveB
            }
            ')
        );
    }

    public function testUniqueDirectivesInSameLocations()
    {
        $this->expectPassesRule(
            new UniqueDirectivesPerLocationRule(),
            dedent('
            fragment Test on Type @directiveA @directiveB {
              field @directiveA @directiveB
            }
            ')
        );
    }

    public function testSameDirectivesInDifferentLocations()
    {
        $this->expectPassesRule(
            new UniqueDirectivesPerLocationRule(),
            dedent('
            fragment Test on Type @directiveA {
              field @directiveA
            }
            ')
        );
    }

    public function testSameDirectivesInSimilarLocations()
    {
        $this->expectPassesRule(
            new UniqueDirectivesPerLocationRule(),
            dedent('
            fragment Test on Type {
              field @directive
              field @directive
            }
            ')
        );
    }

    public function testDuplicateDirectivesInOneLocation()
    {
        $this->expectFailsRule(
            new UniqueDirectivesPerLocationRule(),
            dedent('
            fragment Test on Type {
              field @directive @directive
            }
            '),
            [duplicateDirective('directive', [[2, 9], [2, 20]])]
        );
    }

    public function testManyDuplicateDirectivesInOneLocation()
    {
        $this->expectFailsRule(
            new UniqueDirectivesPerLocationRule(),
            dedent('
            fragment Test on Type {
              field @directive @directive @directive
            }
            '),
            [
                duplicateDirective('directive', [[2, 9], [2, 20]]),
                duplicateDirective('directive', [[2, 9], [2, 31]]),
            ]
        );
    }

    public function testDifferentDuplicateDirectivesInOneLocations()
    {
        $this->expectFailsRule(
            new UniqueDirectivesPerLocationRule(),
            dedent('
            fragment Test on Type {
              field @directiveA @directiveB @directiveA @directiveB
            }
            '),
            [
                duplicateDirective('directiveA', [[2, 9], [2, 33]]),
                duplicateDirective('directiveB', [[2, 21], [2, 45]]),
            ]
        );
    }

    public function testDuplicateDirectivesInManyLocations()
    {
        $this->expectFailsRule(
            new UniqueDirectivesPerLocationRule(),
            dedent('
            fragment Test on Type @directive @directive {
              field @directive @directive
            }
            '),
            [
                duplicateDirective('directive', [[1, 23], [1, 34]]),
                duplicateDirective('directive', [[2, 9], [2, 20]]),
            ]
        );
    }
}
