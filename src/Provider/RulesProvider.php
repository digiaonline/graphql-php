<?php

namespace Digia\GraphQL\Provider;

use Digia\GraphQL\Validation\Rule\ExecutableDefinitionRule;
use League\Container\ServiceProvider\AbstractServiceProvider;

class RulesProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        ExecutableDefinitionRule::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(ExecutableDefinitionRule::class, ExecutableDefinitionRule::class, true/* $shared */);
    }
}
