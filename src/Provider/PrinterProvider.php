<?php

namespace Digia\GraphQL\Provider;

use Digia\GraphQL\Language\Printer;
use Digia\GraphQL\Language\PrinterInterface;
use Digia\GraphQL\Language\Writer\SupportedWriters;
use League\Container\ServiceProvider\AbstractServiceProvider;

class PrinterProvider extends AbstractServiceProvider
{

    /**
     * @var array
     */
    protected $provides = [
        PrinterInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(PrinterInterface::class, Printer::class, true/* $shared */)
            ->withArgument(SupportedWriters::get());
    }
}
