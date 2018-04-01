<?php

namespace Digia\GraphQL\Schema\Validation;

use Digia\GraphQL\Error\SchemaValidationException;
use Digia\GraphQL\Schema\Validation\Rule\RuleInterface;
use Digia\GraphQL\Schema\Validation\Rule\SupportedRules;
use Digia\GraphQL\Schema\SchemaInterface;

class SchemaValidator implements SchemaValidatorInterface
{
    /**
     * @param SchemaInterface      $schema
     * @param RuleInterface[]|null $rules
     * @return SchemaValidationException[]
     */
    public function validate(SchemaInterface $schema, ?array $rules = null): array
    {
        $context = $this->createContext($schema);

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

    /**
     * @inheritdoc
     */
    public function createContext(SchemaInterface $schema): ValidationContextInterface
    {
        return new ValidationContext($schema);
    }
}
