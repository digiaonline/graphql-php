<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Location;

abstract class AbstractNodeBuilder implements NodeBuilderInterface
{
    /**
     * @var NodeDirectorInterface
     */
    protected $director;

    /**
     * @param NodeDirectorInterface $director
     * @return $this
     */
    public function setDirector(NodeDirectorInterface $director)
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
    protected function getValue(array $ast, string $propertyName, $defaultValue = null)
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
    protected function buildNode(array $ast, string $propertyName)
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
    protected function buildNodes(array $ast, string $propertyName): array
    {
        $array = [];

        if (isset($ast[$propertyName]) && \is_array($ast[$propertyName])) {
            foreach ($ast[$propertyName] as $subAst) {
                $array[] = $this->director->build($subAst);
            }
        }

        return $array;
    }
}
