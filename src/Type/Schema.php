<?php

namespace Digia\GraphQL;

use Digia\GraphQL\TypeSystem\AbstractDirective;
use Digia\GraphQL\TypeSystem\Definition\ObjectType;

class Schema
{

    use ASTNodeTrait;

    /**
     * @var ObjectType
     */
    private $queryType;

    /**
     * @var ObjectType
     */
    private $mutationType;

    /**
     * @var AbstractDirective[]
     */
    private $directives = [];

    private $typeMap = [];

    private $implementations = [];

    private $possibleTypeMap = [];

    protected function validate()
    {

    }
}
