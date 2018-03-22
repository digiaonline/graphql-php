<?php

namespace Digia\GraphQL\SchemaValidator;

use Digia\GraphQL\SchemaValidator\Rule\DirectivesRule;
use Digia\GraphQL\SchemaValidator\Rule\RootTypesRule;
use Digia\GraphQL\SchemaValidator\Rule\TypesRule;
use Digia\GraphQL\Util\NameValidator;
use Digia\GraphQL\Util\TypeComparator;
use League\Container\ServiceProvider\AbstractServiceProvider;

class SchemaValidatorProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        ValidationContextCreatorInterface::class,
        SchemaValidatorInterface::class,
        RootTypesRule::class,
        DirectivesRule::class,
        TypesRule::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(ValidationContextCreatorInterface::class, ValidationContextCreator::class, true/* $shared */);
        $this->container->add(SchemaValidatorInterface::class, SchemaValidator::class, true/* $shared */)
            ->withArgument(ValidationContextCreatorInterface::class);

        // Rules
        $this->container->add(RootTypesRule::class, RootTypesRule::class);
        $this->container->add(DirectivesRule::class, DirectivesRule::class)
            ->withArgument(NameValidator::class);
        $this->container->add(TypesRule::class, TypesRule::class)
            ->withArgument(NameValidator::class)
            ->withArgument(TypeComparator::class);
    }
}
