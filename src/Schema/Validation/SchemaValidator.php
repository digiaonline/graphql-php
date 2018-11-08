<?php

namespace Digia\GraphQL\Schema\Validation;

use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Schema\Validation\Rule\RuleInterface;
use Digia\GraphQL\Schema\Validation\Rule\SupportedRules;
use Digia\GraphQL\Validation\ValidationExceptionInterface;

class SchemaValidator implements SchemaValidatorInterface
{
    /**
     * @param Schema               $schema
     * @param RuleInterface[]|null $rules
     * @return ValidationExceptionInterface[]
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
     * @inheritdoc
     */
    public function createContext(Schema $schema): ValidationContextInterface
    {
        return new ValidationContext($schema);
    }
}
