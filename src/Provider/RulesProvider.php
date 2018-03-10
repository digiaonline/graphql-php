<?php

namespace Digia\GraphQL\Provider;

use Digia\GraphQL\Validation\Rule\ExecutableDefinitionRule;
use Digia\GraphQL\Validation\Rule\FieldOnCorrectTypeRule;
use Digia\GraphQL\Validation\Rule\FragmentsOnCompositeTypesRule;
use Digia\GraphQL\Validation\Rule\KnownArgumentNamesRule;
use Digia\GraphQL\Validation\Rule\KnownDirectivesRule;
use Digia\GraphQL\Validation\Rule\KnownFragmentNamesRule;
use Digia\GraphQL\Validation\Rule\KnownTypeNamesRule;
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
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(ExecutableDefinitionRule::class, ExecutableDefinitionRule::class, true/* $shared */);
        $this->container->add(FieldOnCorrectTypeRule::class, FieldOnCorrectTypeRule::class, true/* $shared */);
        $this->container->add(
            FragmentsOnCompositeTypesRule::class,
            FragmentsOnCompositeTypesRule::class,
            true/* $shared */
        );
        $this->container->add(KnownArgumentNamesRule::class, KnownArgumentNamesRule::class, true/* $shared */);
        $this->container->add(KnownDirectivesRule::class, KnownDirectivesRule::class, true/* $shared */);
        $this->container->add(KnownFragmentNamesRule::class, KnownFragmentNamesRule::class, true/* $shared */);
        $this->container->add(KnownTypeNamesRule::class, KnownTypeNamesRule::class, true/* $shared */);

    }
}
