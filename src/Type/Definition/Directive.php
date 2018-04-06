<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Config\ConfigAwareInterface;
use Digia\GraphQL\Config\ConfigAwareTrait;
use Digia\GraphQL\Language\Node\NodeAwareInterface;
use Digia\GraphQL\Language\Node\NodeTrait;

class Directive implements DirectiveInterface, ConfigAwareInterface, NodeAwareInterface, ArgumentsAwareInterface
{

    use ConfigAwareTrait;
    use NodeTrait;
    use NameTrait;
    use DescriptionTrait;
    use ArgumentsTrait;

    /**
     * @var string[]
     */
    protected $locations;

    /**
     * @return string[]
     */
    public function getLocations(): array
    {
        return $this->locations;
    }

    /**
     * @param string[] $locations
     *
     * @return $this
     */
    protected function setLocations(array $locations)
    {
        $this->locations = $locations;

        return $this;
    }
}
