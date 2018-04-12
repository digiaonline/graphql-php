<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;

class Directive implements ASTNodeAwareInterface, ArgumentsAwareInterface
{
    use NameTrait;
    use DescriptionTrait;
    use ArgumentsTrait;
    use ASTNodeTrait;

    /**
     * @var string[]
     */
    protected $locations;

    /**
     * Directive constructor.
     *
     * @param string                       $name
     * @param null|string                  $description
     * @param string[]                     $locations
     * @param array                        $arguments
     * @param DirectiveDefinitionNode|null $astNode
     * @throws InvariantException
     */
    public function __construct(
        string $name,
        ?string $description,
        array $locations,
        array $arguments,
        ?DirectiveDefinitionNode $astNode
    ) {
        $this->name        = $name;
        $this->description = $description;
        $this->locations   = $locations;
        $this->astNode     = $astNode;

        $this->buildArguments($arguments);
    }

    /**
     * @return string[]
     */
    public function getLocations(): array
    {
        return $this->locations;
    }

    /**
     * @param string[] $locations
     * @return $this
     */
    protected function setLocations(array $locations)
    {
        $this->locations = $locations;
        return $this;
    }
}
