<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\FieldDefinitionNode;

class Field implements ASTNodeAwareInterface, ArgumentsAwareInterface
{
    // TODO: Subscription support

    use NameTrait;
    use DescriptionTrait;
    use TypeTrait;
    use ArgumentsTrait;
    use ResolveTrait;
    use DeprecationTrait;
    use ASTNodeTrait;

    /**
     * Field constructor.
     *
     * @param string                                                       $name
     * @param null|string                                                  $description
     * @param TypeInterface|OutputTypeInterface|WrappingTypeInterface|null $type
     * @param Argument[]|array[]                                           $arguments
     * @param callable|null                                                $resolve
     * @param null|string                                                  $deprecationReason
     * @param FieldDefinitionNode|null                                     $astNode
     * @throws InvariantException
     */
    public function __construct(
        string $name,
        ?string $description,
        ?TypeInterface $type,
        array $arguments,
        ?callable $resolve,
        ?string $deprecationReason,
        ?FieldDefinitionNode $astNode
    ) {
        $this->name              = $name;
        $this->description       = $description;
        $this->type              = $type;
        $this->resolveCallback   = $resolve;
        $this->deprecationReason = $deprecationReason;
        $this->astNode           = $astNode;

        $this->buildArguments($arguments);
    }
}
