<?php

namespace Digia\GraphQL\TypeSystem;

use Digia\GraphQL\Type\Definition\ArgumentsTrait;
use Digia\GraphQL\Type\Definition\ConfigTrait;
use Digia\GraphQL\Type\Definition\NameTrait;
use Digia\GraphQL\Type\Directive\DirectiveInterface;
use Digia\GraphQL\TypeSystem\Directive\DirectiveLocationEnum;

abstract class AbstractDirective implements DirectiveInterface
{

    use NameTrait;
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
     */
    public function addLocation(DirectiveLocationEnum $location): void
    {
        $this->locations[] = $location;
    }

    /**
     * @param DirectiveLocationEnum[] $locations
     */
    protected function setLocations(array $locations): void
    {
        $this->locations = array_map(function ($location) {
            $this->addLocation($location);
        }, $locations);
    }
}
