<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Schema\Validation\SchemaValidatorInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

class ValidationProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        ValidatorInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container
            ->share(ValidatorInterface::class, Validator::class)
            ->addArgument(SchemaValidatorInterface::class);
    }
}
