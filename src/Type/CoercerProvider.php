<?php

namespace Digia\GraphQL\Type;

use Digia\GraphQL\Type\Coercer\BooleanCoercer;
use Digia\GraphQL\Type\Coercer\FloatCoercer;
use Digia\GraphQL\Type\Coercer\IntCoercer;
use Digia\GraphQL\Type\Coercer\StringCoercer;
use League\Container\ServiceProvider\AbstractServiceProvider;

class CoercerProvider extends AbstractServiceProvider
{

    /**
     * @var array
     */
    protected $provides = [
        BooleanCoercer::class,
        FloatCoercer::class,
        IntCoercer::class,
        StringCoercer::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(BooleanCoercer::class, BooleanCoercer::class,
            true/* $shared */);
        $this->container->add(FloatCoercer::class, FloatCoercer::class,
            true/* $shared */);
        $this->container->add(IntCoercer::class, IntCoercer::class,
            true/* $shared */);
        $this->container->add(StringCoercer::class, StringCoercer::class,
            true/* $shared */);
    }
}
