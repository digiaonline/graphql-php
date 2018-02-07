<?php

namespace Digia\GraphQL;

use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Directive\DirectiveInterface;

class Schema
{

    use NodeTrait;

    /**
     * @var ObjectType
     */
    private $queryType;

    /**
     * @var ObjectType
     */
    private $mutationType;

    /**
     * @var DirectiveInterface[]
     */
    private $directives = [];

    private $typeMap = [];

    private $implementations = [];

    private $possibleTypeMap = [];
}
