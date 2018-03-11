<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Location;

abstract class AbstractBuilder implements BuilderInterface
{

    /**
     * @var DirectorInterface
     */
    protected $director;

    /**
     * @param DirectorInterface $director
     * @return $this
     */
    public function setDirector(DirectorInterface $director)
    {
        $this->director = $director;
        return $this;
    }

    /**
     * Creates a location object.
     *
     * @param array $ast
     * @return Location|null
     */
    protected function createLocation(array $ast): ?Location
    {
        return isset($ast['loc']['start'], $ast['loc']['end'])
            ? new Location($ast['loc']['start'], $ast['loc']['end'], $ast['loc']['source'] ?? null)
            : null;
    }

    /**
     * Returns the value of a single property in the given AST.
     *
     * @param array  $ast
     * @param string $propertyName
     * @param null   $defaultValue
     * @return mixed|null
     */
    protected function get(array $ast, string $propertyName, $defaultValue = null)
    {
        return $ast[$propertyName] ?? $defaultValue;
    }

    /**
     * Builds a single item from the given AST.
     *
     * @param array  $ast
     * @param string $propertyName
     * @return mixed|null
     */
    protected function buildOne(array $ast, string $propertyName)
    {
        return isset($ast[$propertyName]) ? $this->director->build($ast[$propertyName]) : null;
    }

    /**
     * Builds many items from the given AST.
     *
     * @param array  $ast
     * @param string $propertyName
     * @return array
     */
    protected function buildMany(array $ast, string $propertyName): array
    {
        $array = [];

        if (isset($ast[$propertyName])) {
            foreach ($ast[$propertyName] as $subAst) {
                $array[] = $this->director->build($subAst);
            }
        }

        return $array;
    }
}
