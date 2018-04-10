<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class InputObjectTypeExtensionNode extends AbstractNode implements TypeExtensionNodeInterface,
    DirectivesAwareInterface, NameAwareInterface
{
    use NameTrait;
    use DirectivesTrait;
    use InputFieldsTrait;

    /**
     * InputObjectTypeExtensionNode constructor.
     *
     * @param NameNode                   $name
     * @param DirectiveNode[]            $directives
     * @param InputValueDefinitionNode[] $fields
     * @param Location|null              $location
     */
    public function __construct(
        NameNode $name,
        array $directives,
        array $fields,
        ?Location $location
    ) {
        parent::__construct(NodeKindEnum::INPUT_OBJECT_TYPE_EXTENSION, $location);

        $this->name        = $name;
        $this->directives  = $directives;
        $this->fields      = $fields;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'       => $this->kind,
            'name'       => $this->getNameAsArray(),
            'directives' => $this->getDirectivesAsArray(),
            'fields'     => $this->getFieldsAsArray(),
            'loc'        => $this->getLocationAsArray(),
        ];
    }
}
