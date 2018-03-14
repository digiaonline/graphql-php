<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\NodeBuilder\NodeBuilder;
use Digia\GraphQL\Language\NodeBuilder\NodeBuilderInterface;
use Digia\GraphQL\Language\NodeBuilder\SupportedBuilders;
use Digia\GraphQL\Language\Reader\SupportedReaders;
use Digia\GraphQL\Language\SchemaBuilder\DefinitionBuilder;
use Digia\GraphQL\Language\SchemaBuilder\DefinitionBuilderInterface;
use Digia\GraphQL\Language\SchemaBuilder\SchemaBuilder;
use Digia\GraphQL\Language\SchemaBuilder\SchemaBuilderInterface;
use Digia\GraphQL\Language\Writer\SupportedWriters;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Psr\SimpleCache\CacheInterface;

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
        DefinitionBuilderInterface::class,
        SchemaBuilderInterface::class,
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

        $this->container->add(DefinitionBuilderInterface::class, DefinitionBuilder::class, true/* $shared */)
            ->withArguments([
                function (NamedTypeNode $node) {
                    throw new InvalidTypeException(sprintf('Type "%s" not found in document.', $node->getNameValue()));
                },
                CacheInterface::class,
            ]);

        $this->container->add(SchemaBuilderInterface::class, SchemaBuilder::class, true/* $shared */)
            ->withArgument(DefinitionBuilderInterface::class);
    }
}
