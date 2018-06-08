<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class FieldDefinitionNode extends AbstractNode implements DefinitionNodeInterface, DirectivesAwareInterface,
    NameAwareInterface
{
    use TypeTrait;
    use NameTrait;
    use DescriptionTrait;
    use InputArgumentsTrait;
    use DirectivesTrait;

    /**
     * FieldDefinitionNode constructor.
     *
     * @param StringValueNode|null       $description
     * @param NameNode                   $name
     * @param InputValueDefinitionNode[] $arguments
     * @param TypeNodeInterface          $type
     * @param DirectiveNode[]            $directives
     * @param Location|null              $location
     */
    public function __construct(
        ?StringValueNode $description,
        NameNode $name,
        array $arguments,
        TypeNodeInterface $type,
        array $directives,
        ?Location $location
    ) {
        parent::__construct(NodeKindEnum::FIELD_DEFINITION, $location);

        $this->description = $description;
        $this->name        = $name;
        $this->arguments   = $arguments;
        $this->type        = $type;
        $this->directives  = $directives;
    }

    /**
     * @inheritdoc
     */
    public function toAST(): array
    {
        return [
            'kind'        => $this->kind,
            'description' => $this->description,
            'name'        => $this->getNameAST(),
            'arguments'   => $this->getArgumentsAST(),
            'type'        => $this->getTypeAST(),
            'directives'  => $this->getDirectivesAST(),
            'loc'         => $this->getLocationAST(),
        ];
    }
}
