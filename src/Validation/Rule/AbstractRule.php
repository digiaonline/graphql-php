<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Language\Visitor\SpecificKindVisitor;
use Digia\GraphQL\Validation\ValidationContextAwareTrait;

abstract class AbstractRule extends SpecificKindVisitor implements RuleInterface
{

    use ValidationContextAwareTrait;
}
