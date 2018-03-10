<?php

namespace Digia\GraphQL\Provider;

use Digia\GraphQL\Validation\Rule\ExecutableDefinitionRule;
use Digia\GraphQL\Validation\Rule\FieldOnCorrectTypeRule;
use Digia\GraphQL\Validation\Rule\FragmentsOnCompositeTypesRule;
use Digia\GraphQL\Validation\Rule\KnownArgumentNamesRule;
use Digia\GraphQL\Validation\Rule\KnownDirectivesRule;
use Digia\GraphQL\Validation\Rule\KnownFragmentNamesRule;
use Digia\GraphQL\Validation\Rule\KnownTypeNamesRule;
use Digia\GraphQL\Validation\Rule\LoneAnonymousOperationRule;
use Digia\GraphQL\Validation\Rule\NoFragmentCyclesRule;
use Digia\GraphQL\Validation\Rule\NoUndefinedVariablesRule;
use Digia\GraphQL\Validation\Rule\NoUnusedFragmentsRule;
use Digia\GraphQL\Validation\Rule\OverlappingFieldsCanBeMergedRule;
use League\Container\ServiceProvider\AbstractServiceProvider;

class RulesProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        ExecutableDefinitionRule::class,
        FieldOnCorrectTypeRule::class,
        FragmentsOnCompositeTypesRule::class,
        KnownArgumentNamesRule::class,
        KnownDirectivesRule::class,
        KnownFragmentNamesRule::class,
        KnownTypeNamesRule::class,
        LoneAnonymousOperationRule::class,
        NoFragmentCyclesRule::class,
        NoUndefinedVariablesRule::class,
        NoUnusedFragmentsRule::class,
        OverlappingFieldsCanBeMergedRule::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        foreach ($this->provides as $className) {
            $this->container->add($className, $className, true/* $shared */);
        }
    }
}