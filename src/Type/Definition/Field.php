<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\FieldDefinitionNode;

class Field implements ASTNodeAwareInterface, ArgumentsAwareInterface
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
     * @param string                                                       $name
     * @param null|string                                                  $description
     * @param TypeInterface|OutputTypeInterface|WrappingTypeInterface|null $type
     * @param Argument[]|array[]                                           $arguments
     * @param callable|null                                                $resolveCallback
     * @param callable|null                                                $subscribeCallback
     * @param null|string                                                  $deprecationReason
     * @param FieldDefinitionNode|null                                     $astNode
     * @throws InvariantException
     */
    public function __construct(
        string $name,
        ?string $description,
        ?TypeInterface $type,
        array $arguments,
        ?callable $resolveCallback,
        ?callable $subscribeCallback,
        ?string $deprecationReason,
        ?FieldDefinitionNode $astNode
    ) {
        $this->name              = $name;
        $this->description       = $description;
        $this->type              = $type;
        $this->resolveCallback   = $resolveCallback;
        $this->subscribeCallback = $subscribeCallback;
        $this->deprecationReason = $deprecationReason;
        $this->astNode           = $astNode;

        $this->buildArguments($arguments);
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
