<?php

namespace Digia\GraphQL\Type;

use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Directive\DirectiveInterface;

/**
 * Class Schema
 *
 * @package Digia\GraphQL\Type
 * @codeCoverageIgnore
 */
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
