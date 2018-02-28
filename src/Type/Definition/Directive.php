<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\ConfigObject;
use Digia\GraphQL\Type\Definition\ArgumentsTrait;
use Digia\GraphQL\Type\Definition\DescriptionTrait;
use Digia\GraphQL\Type\Definition\NameTrait;
use Digia\GraphQL\Type\Definition\DirectiveInterface;

class Directive extends ConfigObject implements DirectiveInterface
{

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
