<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\InputValueDefinitionNode;
use Digia\GraphQL\Schema\Definition;
use GraphQL\Contracts\TypeSystem\ArgumentInterface;
use GraphQL\Contracts\TypeSystem\Type\TypeInterface;

class Argument extends Definition implements ArgumentInterface, InputValueInterface, ASTNodeAwareInterface
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
