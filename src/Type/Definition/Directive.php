<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Schema\Definition;

class Directive extends Definition implements
    ASTNodeAwareInterface,
    ArgumentsAwareInterface,
    DescriptionAwareInterface
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
     * @param array                        $rawArguments
     * @param DirectiveDefinitionNode|null $astNode
     * @param string                       $typeName
     * @throws InvariantException
     */
    public function __construct(
        string $name,
        ?string $description,
        array $locations,
        array $rawArguments,
        ?DirectiveDefinitionNode $astNode,
        string $typeName
    ) {
        $this->name         = $name;
        $this->description  = $description;
        $this->locations    = $locations;
        $this->rawArguments = $rawArguments;
        $this->astNode      = $astNode;

        $this->arguments = $this->buildArguments($typeName, $this->rawArguments);
    }

    /**
     * @return string[]
     */
    public function getLocations(): array
    {
        return $this->locations;
    }
}
