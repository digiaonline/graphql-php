<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Type\Definition\Contract\TypeInterface;

abstract class AbstractTypeTestCase extends TestCase
{

    /**
     * @var array
     */
    protected $config;

    /**
     * @var TypeInterface
     */
    protected $type;
}
