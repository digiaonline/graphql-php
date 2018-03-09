<?php

namespace Digia\GraphQL\Provider;

use Digia\GraphQL\Validation\Rule\ExecutableDefinitionRule;
use Digia\GraphQL\Validation\Rule\FieldOnCorrectTypeRule;
use Digia\GraphQL\Validation\Rule\FragmentsOnCompositeTypesRule;
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
    }
}
