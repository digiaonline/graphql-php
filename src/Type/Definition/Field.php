<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\FieldDefinitionNode;
use Digia\GraphQL\Schema\Definition;
use GraphQL\Contracts\TypeSystem\Type\TypeInterface;

class Field extends Definition implements
    FieldInterface,
    ASTNodeAwareInterface,
    ArgumentsAwareInterface,
    DescriptionAwareInterface,
    DeprecationAwareInterface
{
    use NameTrait;
    use DescriptionTrait;
    use TypeTrait;
    use ArgumentsTrait;
    use ResolveTrait;
    use DeprecationTrait;
    use ASTNodeTrait;

    /**
     * @var callable|null
     */
    protected $subscribeCallback;

    /**
     * Field constructor.
     *
     * @param string                   $name
     * @param null|string              $description
     * @param TypeInterface|null       $type
     * @param Argument[]|array[]       $rawArguments
     * @param callable|null            $resolveCallback
     * @param callable|null            $subscribeCallback
     * @param null|string              $deprecationReason
     * @param FieldDefinitionNode|null $astNode
     * @param string                   $typeName
     * @throws InvariantException
     */
    public function __construct(
        string $name,
        ?string $description,
        ?TypeInterface $type,
        array $rawArguments,
        ?callable $resolveCallback,
        ?callable $subscribeCallback,
        ?string $deprecationReason,
        ?FieldDefinitionNode $astNode,
        string $typeName
    ) {
        $this->name              = $name;
        $this->description       = $description;
        $this->type              = $type;
        $this->rawArguments      = $rawArguments;
        $this->resolveCallback   = $resolveCallback;
        $this->subscribeCallback = $subscribeCallback;
        $this->deprecationReason = $deprecationReason;
        $this->astNode           = $astNode;

        $this->arguments = $this->buildArguments($typeName, $this->rawArguments);
    }

    /**
     * @param array ...$args
     * @return mixed
     */
    public function subscribe(...$args)
    {
        return null !== $this->subscribeCallback
            ? \call_user_func_array($this->subscribeCallback, $args)
            : null;
    }

    /**
     * @return bool
     */
    public function hasSubscribeCallback()
    {
        return null !== $this->subscribeCallback;
    }

    /**
     * @return callable|null
     */
    public function getSubscribeCallback(): ?callable
    {
        return $this->subscribeCallback;
    }
}
