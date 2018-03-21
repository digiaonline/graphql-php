<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\SchemaValidator\SchemaValidatorInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

class ValidationProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        ValidationContextCreatorInterface::class,
        ValidatorInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(
            ValidationContextCreatorInterface::class,
            ValidationContextCreator::class,
            true/* $shared */
        );

        $this->container->add(ValidatorInterface::class, Validator::class, true/* $shared */)
            ->withArgument(ValidationContextCreatorInterface::class)
            ->withArgument(SchemaValidatorInterface::class);
    }
}
