<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\InputValueDefinitionNode;

class InputField implements FieldInterface, ASTNodeAwareInterface, DescriptionAwareInterface
{
    use NameTrait;
    use TypeTrait;
    use DefaultValueTrait;
    use DescriptionTrait;
    use ASTNodeTrait;

    /**
     * InputField constructor.
     *
     * @param string                        $name
     * @param TypeInterface|null            $type
     * @param mixed|null                    $defaultValue
     * @param null|string                   $description
     * @param InputValueDefinitionNode|null $astNode
     */
    public function __construct(
        string $name,
        ?string $description,
        ?TypeInterface $type,
        $defaultValue,
        ?InputValueDefinitionNode $astNode
    ) {
        $this->name         = $name;
        $this->type         = $type;
        $this->defaultValue = $defaultValue;
        $this->description  = $description;
        $this->astNode      = $astNode;
    }
}
