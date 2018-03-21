<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Language\ASTBuilder\ASTDirector;
use Digia\GraphQL\Language\ASTBuilder\ASTDirectorInterface;
use Digia\GraphQL\Language\ASTBuilder\SupportedASTBuilders;
use Digia\GraphQL\Language\NodeBuilder\ASTBuilder;
use Digia\GraphQL\Language\NodeBuilder\NodeDirector;
use Digia\GraphQL\Language\NodeBuilder\NodeDirectorInterface;
use Digia\GraphQL\Language\NodeBuilder\SupportedNodeBuilders;
use Digia\GraphQL\Language\Reader\SupportedReaders;
use Digia\GraphQL\Language\Writer\SupportedWriters;
use League\Container\ServiceProvider\AbstractServiceProvider;

class LanguageProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        ASTDirectorInterface::class,
        NodeDirectorInterface::class,
        LexerInterface::class,
        ParserInterface::class,
        PrinterInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(ASTDirectorInterface::class, function () {
            return new ASTDirector(SupportedASTBuilders::get());
        });

        $this->container->add(NodeDirectorInterface::class, function () {
            return new NodeDirector(SupportedNodeBuilders::get());
        });

        $this->container->add(ParserInterface::class, Parser::class, true/* $shared */)
            ->withArgument(ASTDirectorInterface::class)
            ->withArgument(NodeDirectorInterface::class);

        $this->container->add(LexerInterface::class, function () {
            return new Lexer(SupportedReaders::get());
        });

        $this->container->add(PrinterInterface::class, Printer::class, true/* $shared */)
            ->withArgument(SupportedWriters::get());
    }
}
