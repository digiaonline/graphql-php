<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\ScalarLeafsRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\noSubselectionAllowed;
use function Digia\GraphQL\Test\Functional\Validation\requiredSubselection;

class ScalarLeafsRuleTest extends RuleTestCase
{

    public function testValidScalarSelection()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment scalarSelection on Dog {
              barks
            }
            ')
        );
    }

    public function testObjectTypeMissingSelection()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            query directQueryOnObjectWithoutSubFields {
              human
            }
            '),
            [requiredSubselection('human', 'Human', [2, 3])]
        );
    }

    public function testInterfaceTypeMissingSelection()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              human { pets }
            }
            '),
            [requiredSubselection('pets', '[Pet]', [2, 11])]
        );
    }

    public function testValidScalarSelectionWithArguments()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment scalarSelectionWithArgs on Dog {
              doesKnowCommand(dogCommand: SIT)
            }
            ')
        );
    }

    public function testScalarSelectionNotAllowedOnBoolean()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment scalarSelectionsNotAllowedOnBoolean on Dog {
              barks { sinceWhen }
            }
            '),
            [noSubselectionAllowed('barks', 'Boolean', [2, 9])]
        );
    }

    public function testScalarSelectionNotAllowedOnEnum()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment scalarSelectionsNotAllowedOnEnum on Cat {
              furColor { inHexdec }
            }
            '),
            [noSubselectionAllowed('furColor', 'FurColor', [2, 12])]
        );
    }

    public function testScalarSelectionNotAllowedWithArguments()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment scalarSelectionsNotAllowedWithArgs on Dog {
              doesKnowCommand(dogCommand: SIT) { sinceWhen }
            }
            '),
            [noSubselectionAllowed('doesKnowCommand', 'Boolean', [2, 36])]
        );
    }

    public function testScalarSelectionNotAllowedWithDirectives()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment scalarSelectionsNotAllowedWithDirectives on Dog {
              name @include(if: true) { isAlsoHumanName }
            }
            '),
            [noSubselectionAllowed('name', 'String', [2, 27])]
        );
    }

    public function testScalarSelectionNotAllowedWithDirectivesAndArguments()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment scalarSelectionsNotAllowedWithDirectivesAndArgs on Dog {
              doesKnowCommand(dogCommand: SIT) @include(if: true) { sinceWhen }
            }
            '),
            [noSubselectionAllowed('doesKnowCommand', 'Boolean', [2, 55])]
        );
    }

    protected function getRuleClassName(): string
    {
        return ScalarLeafsRule::class;
    }
}
