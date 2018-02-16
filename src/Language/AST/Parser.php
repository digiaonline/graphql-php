<?php

namespace Digia\GraphQL\Language\AST;

use Digia\GraphQL\Language\AST\Builder\Contract\NodeFactoryInterface;
use Digia\GraphQL\Language\AST\Builder\DocumentNodeBuilder;
use Digia\GraphQL\Language\AST\Builder\FieldNodeBuilder;
use Digia\GraphQL\Language\AST\Builder\NameNodeBuilder;
use Digia\GraphQL\Language\AST\Builder\NodeFactory;
use Digia\GraphQL\Language\AST\Builder\OperationDefinitionNodeBuilder;
use Digia\GraphQL\Language\AST\Builder\SelectionSetNodeBuilder;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use GraphQL\Parser as GraphQLParser;

class Parser
{

    /**
     * @var GraphQLParser
     */
    protected $parser;

    /**
     * @var NodeFactoryInterface
     */
    protected $nodeFactory;

    /**
     * Parser constructor.
     */
    public function __construct()
    {
        $this->parser = new GraphQLParser();

        $builders = [
            new DocumentNodeBuilder(),
            new OperationDefinitionNodeBuilder(),
            new SelectionSetNodeBuilder(),
            new FieldNodeBuilder(),
            new NameNodeBuilder(),
        ];

        $this->nodeFactory = new NodeFactory($builders);
    }

    /**
     * @param string $input
     * @return NodeInterface
     */
    public function parse(string $input): NodeInterface
    {
        $ast = $this->parser->parse($input);

        $node = $this->nodeFactory->build($ast);

        var_dump($node);die;

        return $node;
    }
}
