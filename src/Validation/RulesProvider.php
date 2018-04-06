<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Validation\Rule\ExecutableDefinitionsRule;
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
use Digia\GraphQL\Validation\Rule\NoUnusedVariablesRule;
use Digia\GraphQL\Validation\Rule\OverlappingFieldsCanBeMergedRule;
use Digia\GraphQL\Validation\Rule\PossibleFragmentSpreadsRule;
use Digia\GraphQL\Validation\Rule\ProvidedNonNullArgumentsRule;
use Digia\GraphQL\Validation\Rule\ScalarLeafsRule;
use Digia\GraphQL\Validation\Rule\SingleFieldSubscriptionsRule;
use Digia\GraphQL\Validation\Rule\UniqueArgumentNamesRule;
use Digia\GraphQL\Validation\Rule\UniqueDirectivesPerLocationRule;
use Digia\GraphQL\Validation\Rule\UniqueFragmentNamesRule;
use Digia\GraphQL\Validation\Rule\UniqueInputFieldNamesRule;
use Digia\GraphQL\Validation\Rule\UniqueOperationNamesRule;
use Digia\GraphQL\Validation\Rule\UniqueVariableNamesRule;
use Digia\GraphQL\Validation\Rule\ValuesOfCorrectTypeRule;
use Digia\GraphQL\Validation\Rule\VariablesAreInputTypesRule;
use Digia\GraphQL\Validation\Rule\VariablesDefaultValueAllowedRule;
use Digia\GraphQL\Validation\Rule\VariablesInAllowedPositionRule;
use League\Container\ServiceProvider\AbstractServiceProvider;

class RulesProvider extends AbstractServiceProvider
{

    /**
     * @var array
     */
    protected $provides = [
        ExecutableDefinitionsRule::class,
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
        NoUnusedVariablesRule::class,
        OverlappingFieldsCanBeMergedRule::class,
        PossibleFragmentSpreadsRule::class,
        ProvidedNonNullArgumentsRule::class,
        ScalarLeafsRule::class,
        SingleFieldSubscriptionsRule::class,
        UniqueArgumentNamesRule::class,
        UniqueDirectivesPerLocationRule::class,
        UniqueFragmentNamesRule::class,
        UniqueVariableNamesRule::class,
        ValuesOfCorrectTypeRule::class,
        VariablesAreInputTypesRule::class,
        VariablesDefaultValueAllowedRule::class,
        VariablesInAllowedPositionRule::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(ExecutableDefinitionsRule::class,
            ExecutableDefinitionsRule::class);
        $this->container->add(FieldOnCorrectTypeRule::class,
            FieldOnCorrectTypeRule::class);
        $this->container->add(FragmentsOnCompositeTypesRule::class,
            FragmentsOnCompositeTypesRule::class);
        $this->container->add(KnownArgumentNamesRule::class,
            KnownArgumentNamesRule::class);
        $this->container->add(KnownDirectivesRule::class,
            KnownDirectivesRule::class);
        $this->container->add(KnownFragmentNamesRule::class,
            KnownFragmentNamesRule::class);
        $this->container->add(KnownTypeNamesRule::class,
            KnownTypeNamesRule::class);
        $this->container->add(LoneAnonymousOperationRule::class,
            LoneAnonymousOperationRule::class);
        $this->container->add(NoFragmentCyclesRule::class,
            NoFragmentCyclesRule::class);
        $this->container->add(NoUndefinedVariablesRule::class,
            NoUndefinedVariablesRule::class);
        $this->container->add(NoUnusedFragmentsRule::class,
            NoUnusedFragmentsRule::class);
        $this->container->add(NoUnusedVariablesRule::class,
            NoUnusedVariablesRule::class);
        $this->container->add(OverlappingFieldsCanBeMergedRule::class,
            OverlappingFieldsCanBeMergedRule::class);
        $this->container->add(PossibleFragmentSpreadsRule::class,
            PossibleFragmentSpreadsRule::class);
        $this->container->add(ProvidedNonNullArgumentsRule::class,
            ProvidedNonNullArgumentsRule::class);
        $this->container->add(ScalarLeafsRule::class, ScalarLeafsRule::class);
        $this->container->add(SingleFieldSubscriptionsRule::class,
            SingleFieldSubscriptionsRule::class);
        $this->container->add(UniqueArgumentNamesRule::class,
            UniqueArgumentNamesRule::class);
        $this->container->add(UniqueDirectivesPerLocationRule::class,
            UniqueDirectivesPerLocationRule::class);
        $this->container->add(UniqueFragmentNamesRule::class,
            UniqueFragmentNamesRule::class);
        $this->container->add(UniqueInputFieldNamesRule::class,
            UniqueInputFieldNamesRule::class);
        $this->container->add(UniqueOperationNamesRule::class,
            UniqueOperationNamesRule::class);
        $this->container->add(UniqueVariableNamesRule::class,
            UniqueVariableNamesRule::class);
        $this->container->add(ValuesOfCorrectTypeRule::class,
            ValuesOfCorrectTypeRule::class);
        $this->container->add(VariablesAreInputTypesRule::class,
            VariablesAreInputTypesRule::class);
        $this->container->add(VariablesDefaultValueAllowedRule::class,
            VariablesDefaultValueAllowedRule::class);
        $this->container->add(VariablesInAllowedPositionRule::class,
            VariablesInAllowedPositionRule::class);
    }
}
