<?php

namespace Digia\GraphQL\Schema\Validation;

use Digia\GraphQL\Error\SchemaValidationException;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Schema\Validation\Rule\RuleInterface;
use Digia\GraphQL\Schema\Validation\Rule\SupportedRules;

class SchemaValidator implements SchemaValidatorInterface
{
    /**
     * @param Schema               $schema
     * @param RuleInterface[]|null $rules
     * @return SchemaValidationException[]
     */
    public function validate(Schema $schema, ?array $rules = null): array
    {
        $context = $this->createContext($schema);

        $rules = $rules ?? SupportedRules::build();

        foreach ($rules as $rule) {
            $rule->setContext($context)->evaluate();
        }

        return $context->getErrors();
    }

    /**
     * @param Schema $schema
     * @throws SchemaValidationException
     */
    public function assertValid(Schema $schema): void
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
    public function createContext(Schema $schema): ValidationContextInterface
    {
        return new ValidationContext($schema);
    }
}
