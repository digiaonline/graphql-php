<?php

namespace Digia\GraphQL\TypeSystem;

use Digia\GraphQL\Type\Definition\ArgumentsTrait;
use Digia\GraphQL\Behavior\ConfigTrait;
use Digia\GraphQL\Type\Definition\DescriptionTrait;
use Digia\GraphQL\Type\Definition\NameTrait;
use Digia\GraphQL\Type\Directive\DirectiveInterface;
use Digia\GraphQL\TypeSystem\Directive\DirectiveLocationEnum;

abstract class AbstractDirective implements DirectiveInterface
{

    use NameTrait;
    use DescriptionTrait;
    use ArgumentsTrait;
    use ConfigTrait;

    /**
     * @var DirectiveLocationEnum[]
     */
    private $locations;

    /**
     * @return DirectiveLocationEnum[]
     */
    public function getLocations(): array
    {
        return $this->locations;
    }

    /**
     * @param DirectiveLocationEnum $location
     * @return $this
     */
    public function addLocation(DirectiveLocationEnum $location)
    {
        $this->locations[] = $location;

        return $this;
    }

    /**
     * @param DirectiveLocationEnum[] $locations
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
