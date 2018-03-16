<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Language\NodeBuilder\NodeBuilder;
use Digia\GraphQL\Language\NodeBuilder\NodeBuilderInterface;
use Digia\GraphQL\Language\NodeBuilder\SupportedBuilders;
use Digia\GraphQL\Language\Reader\SupportedReaders;
use Digia\GraphQL\Language\Writer\SupportedWriters;
use League\Container\ServiceProvider\AbstractServiceProvider;

class LanguageProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        NodeBuilderInterface::class,
        LexerInterface::class,
        ParserInterface::class,
        PrinterInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(NodeBuilderInterface::class, function () {
            return new NodeBuilder(SupportedBuilders::get());
        });

        $this->container->add(ParserInterface::class, Parser::class, true/* $shared */)
            ->withArgument(NodeBuilderInterface::class);

        $this->container->add(LexerInterface::class, function () {
            return new Lexer(SupportedReaders::get());
        });

        $this->container->add(PrinterInterface::class, Printer::class, true/* $shared */)
            ->withArgument(SupportedWriters::get());
    }
}
