<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Language\AST\Visitor\VisitorBreak;
use Digia\GraphQL\Validation\Rule\KnownArgumentNamesRule;
use function Digia\GraphQL\Validation\Rule\unknownArgumentMessage;
use function Digia\GraphQL\Validation\Rule\unknownDirectiveArgumentMessage;

/**
 * @param string $argumentName
 * @param string $fieldName
 * @param string $typeName
 * @param array  $suggestedArguments
 * @param int    $line
 * @param int    $column
 * @return array
 */
function unknownArgument(
    string $argumentName,
    string $fieldName,
    string $typeName,
    array $suggestedArguments,
    int $line,
    int $column
) {
    return [
        'message'   => unknownArgumentMessage($argumentName, $fieldName, $typeName, $suggestedArguments),
        // TODO: Add locations when support has been added to GraphQLError.
        'locations' => null, //[['line' => $line, 'column' => $column]],
        'path'      => null,
    ];
}

function unknownDirectiveArgument(
    string $argumentName,
    string $directiveName,
    array $suggestedArguments,
    int $line,
    int $column
) {
    return [
        'message'   => unknownDirectiveArgumentMessage($argumentName, $directiveName, $suggestedArguments),
        // TODO: Add locations when support has been added to GraphQLError.
        'locations' => null, //[['line' => $line, 'column' => $column]],
        'path'      => null,
    ];
}

class KnownArgumentNamesRuleTest extends RuleTestCase
{
    /**
     * @throws VisitorBreak
     * @throws \Exception
     * @throws \TypeError
     */
    public function testSingleArgumentIsKnown()
    {
        $this->expectPassesRule(
            new KnownArgumentNamesRule(),
            '
            fragment argOnRequiredArg on Dog {
              doesKnownCommand(dogCommand: SIT)
            }
            '
        );
    }
}
