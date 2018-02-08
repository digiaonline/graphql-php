<?php

namespace Digia\GraphQL\Type\Directive;

use Digia\GraphQL\Type\Behavior\ArgumentsTrait;
use Digia\GraphQL\Type\Behavior\NameTrait;
use Digia\GraphQL\Behavior\ConfigTrait;
use Digia\GraphQL\Behavior\DescriptionTrait;

abstract class AbstractDirective implements DirectiveInterface
{

    use NameTrait;
    use DescriptionTrait;
    use ArgumentsTrait;
    use ConfigTrait;

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
    public function addLocation(string $location)
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
