<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\InputValueDefinitionNode;

class Argument implements InputValueInterface, ASTNodeAwareInterface, DescriptionAwareInterface
{
    use NameTrait;
    use TypeTrait;
    use DefaultValueTrait;
    use DescriptionTrait;
    use ASTNodeTrait;

    /**
     * Argument constructor.
     *
     * @param string                        $name
     * @param null|string                   $description
     * @param TypeInterface|null            $type
     * @param mixed|null                    $defaultValue
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
        $this->description  = $description;
        $this->type         = $type;
        $this->defaultValue = $defaultValue;
        $this->astNode      = $astNode;
    }
}
