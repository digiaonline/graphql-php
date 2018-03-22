<?php

namespace Digia\GraphQL\SchemaValidator;

use Digia\GraphQL\Error\SchemaValidationException;
use Digia\GraphQL\SchemaValidator\Rule\RuleInterface;
use Digia\GraphQL\SchemaValidator\Rule\SupportedRules;
use Digia\GraphQL\Type\SchemaInterface;

class SchemaValidator implements SchemaValidatorInterface
{
    /**
     * @var ValidationContextCreatorInterface
     */
    protected $contextCreator;

    /**
     * SchemaValidator constructor.
     * @param ValidationContextCreatorInterface $contextCreator
     */
    public function __construct(ValidationContextCreatorInterface $contextCreator)
    {
        $this->contextCreator = $contextCreator;
    }

    /**
     * @param SchemaInterface      $schema
     * @param RuleInterface[]|null $rules
     * @return SchemaValidationException[]
     */
    public function validate(SchemaInterface $schema, ?array $rules = null): array
    {
        $context = $this->contextCreator->create($schema);

        $rules = $rules ?? SupportedRules::build();

        foreach ($rules as $rule) {
            $rule->setContext($context)->evaluate();
        }

        return $context->getErrors();
    }

    /**
     * @param SchemaInterface $schema
     * @throws SchemaValidationException
     */
    public function assertValid(SchemaInterface $schema): void
    {
        $errors = $this->validate($schema);

        if (!empty($errors)) {
            $message = \implode("\n", \array_map(function (SchemaValidationException $error) {
                return $error->getMessage();
            }, $errors));

            throw new SchemaValidationException($message);
        }
    }
}
