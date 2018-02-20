<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Builder\Contract\BuilderInterface;
use Digia\GraphQL\Language\AST\Builder\Contract\DirectorInterface;

abstract class AbstractBuilder implements BuilderInterface
{

    /**
     * @var DirectorInterface
     */
    protected $director;

    /**
     * @return DirectorInterface
     */
    public function getDirector(): DirectorInterface
    {
        return $this->director;
    }

    /**
     * @param DirectorInterface $director
     * @return $this
     */
    public function setDirector(DirectorInterface $director)
    {
        $this->director = $director;
        return $this;
    }
}
