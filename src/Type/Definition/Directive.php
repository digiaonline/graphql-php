<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Config\ConfigObject;
use Digia\GraphQL\Language\Node\NodeAwareInterface;
use Digia\GraphQL\Language\Node\NodeTrait;

class Directive extends ConfigObject implements DirectiveInterface, NodeAwareInterface
{
    use NodeTrait;
    use NameTrait;
    use DescriptionTrait;
    use ArgumentsTrait;

    /**
     * @var string[]
     */
    private $locations;

    /**
     * @return string[]
     */
    public function getLocations(): array
    {
        return $this->locations;
    }

    /**
     * @param string $location
     * @return $this
     */
    protected function addLocation(string $location)
    {
        $this->locations[] = $location;

        return $this;
    }

    /**
     * @param string[] $locations
     * @return $this
     */
    protected function setLocations(array $locations)
    {
        foreach ($locations as $location) {
            $this->addLocation($location);
        }

        return $this;
    }
}
