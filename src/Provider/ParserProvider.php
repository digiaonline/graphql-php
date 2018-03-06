<?php

namespace Digia\GraphQL\Provider;

use Digia\GraphQL\Language\AST\Builder\NodeBuilder;
use Digia\GraphQL\Language\AST\Builder\NodeBuilderInterface;
use Digia\GraphQL\Language\AST\Builder\SupportedBuilders;
use Digia\GraphQL\Language\Lexer;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Parser;
use Digia\GraphQL\Language\ParserInterface;
use Digia\GraphQL\Language\Reader\SupportedReaders;
use League\Container\ServiceProvider\AbstractServiceProvider;

class ParserProvider extends AbstractServiceProvider
{

    /**
     * @var array
     */
    protected $provides = [
        NodeBuilderInterface::class,
        LexerInterface::class,
        ParserInterface::class,
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
    }
}
