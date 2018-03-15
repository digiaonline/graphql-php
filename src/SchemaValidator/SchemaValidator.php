<?php

namespace Digia\GraphQL\SchemaValidator;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\SchemaValidator\Rule\RuleInterface;
use Digia\GraphQL\SchemaValidator\Rule\SupportedRules;
use Digia\GraphQL\Type\SchemaInterface;

class SchemaValidator implements SchemaValidatorInterface
{
    /**
     * @var ContextBuilderInterface
     */
    protected $contextBuilder;

    /**
     * SchemaValidator constructor.
     * @param ContextBuilderInterface $contextBuilder
     */
    public function __construct(ContextBuilderInterface $contextBuilder)
    {
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param SchemaInterface      $schema
     * @param RuleInterface[]|null $rules
     * @return ValidationException[]
     */
    public function validate(SchemaInterface $schema, ?array $rules = null): array
    {
        $context = $this->contextBuilder->build($schema);

        $rules = $rules ?? SupportedRules::build();

        foreach ($rules as $rule) {
            $rule
                ->setContext($context)
                ->evaluate();
        }

        return $context->getErrors();
    }

    /**
     * @param SchemaInterface $schema
     * @throws ValidationException
     */
    public function assertValid(SchemaInterface $schema): void
    {
        $errors = $this->validate($schema);

        if (!empty($errors)) {
            $message = \implode("\n", \array_map(function (ValidationException $error) {
                return $error->getMessage();
            }, $errors));

            throw new ValidationException($message);
        }
    }
}
