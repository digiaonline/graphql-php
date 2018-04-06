<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Language\Reader\SupportedReaders;
use Digia\GraphQL\Language\Writer\SupportedWriters;
use League\Container\ServiceProvider\AbstractServiceProvider;

class LanguageProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        ASTBuilderInterface::class,
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
        $this->container->add(ASTBuilderInterface::class, ASTBuilder::class, true/* $shared */);
        $this->container->add(NodeBuilderInterface::class, NodeBuilder::class, true/* $shared */);

        $this->container->add(ParserInterface::class, Parser::class, true/* $shared */)
            ->withArgument(ASTBuilderInterface::class)
            ->withArgument(NodeBuilderInterface::class);

        $this->container->add(TokenReaderInterface::class, TokenReader::class, true/* $shared */);
        $this->container->add(LexerInterface::class, Lexer::class)
            ->withArgument(TokenReaderInterface::class);

        $this->container->add(PrinterInterface::class, Printer::class, true/* $shared */)
            ->withArgument(SupportedWriters::get());
    }
}
