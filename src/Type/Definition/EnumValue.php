<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\EnumValueDefinitionNode;

class EnumValue implements ASTNodeAwareInterface
{
    use NameTrait;
    use DescriptionTrait;
    use DeprecationTrait;
    use ValueTrait;
    use ASTNodeTrait;

    /**
     * EnumValue constructor.
     *
     * @param string                       $name
     * @param null|string                  $description
     * @param null|string                  $deprecationReason
     * @param EnumValueDefinitionNode|null $astNode
     * @param mixed                        $value
     */
    public function __construct(
        string $name,
        ?string $description,
        ?string $deprecationReason,
        ?EnumValueDefinitionNode $astNode,
        $value
    ) {
        $this->name              = $name;
        $this->description       = $description;
        $this->deprecationReason = $deprecationReason;
        $this->astNode           = $astNode;
        $this->value             = $value ?? $this->name; // By default, enum values use their value as their name.
    }
}
