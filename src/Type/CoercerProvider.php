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
        $this->container->share(BooleanCoercer::class, BooleanCoercer::class);
        $this->container->share(FloatCoercer::class, FloatCoercer::class);
        $this->container->share(IntCoercer::class, IntCoercer::class);
        $this->container->share(StringCoercer::class, StringCoercer::class);
    }
}
