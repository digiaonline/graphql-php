<?php

namespace Digia\GraphQL\Schema\Validation;

use Digia\GraphQL\Error\SchemaValidationException;
use Digia\GraphQL\Schema\Validation\Rule\RuleInterface;
use Digia\GraphQL\Schema\Validation\Rule\SupportedRules;
use Digia\GraphQL\Schema\SchemaInterface;

class SchemaValidator implements SchemaValidatorInterface
{
    /**
     * @var ValidationContextCreatorInterface
     */
    protected $contextCreator;

    /**
     * SchemaValidation constructor.
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
