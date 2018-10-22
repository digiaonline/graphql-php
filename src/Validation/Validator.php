<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Visitor\ParallelVisitor;
use Digia\GraphQL\Language\Visitor\TypeInfoVisitor;
use Digia\GraphQL\Language\Visitor\VisitorInfo;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Schema\Validation\SchemaValidatorInterface;
use Digia\GraphQL\Util\TypeInfo;
use Digia\GraphQL\Validation\Rule\RuleInterface;
use Digia\GraphQL\Validation\Rule\SupportedRules;

class Validator implements ValidatorInterface
{
    /**
     * @var SchemaValidatorInterface
     */
    protected $schemaValidator;

    /**
     * Validator constructor.
     * @param SchemaValidatorInterface $schemaValidator
     */
    public function __construct(SchemaValidatorInterface $schemaValidator)
    {
        $this->schemaValidator = $schemaValidator;
    }

    /**
     * @inheritdoc
     */
    public function validate(
        Schema $schema,
        DocumentNode $document,
        ?array $rules = null,
        ?TypeInfo $typeInfo = null
    ): array {
        $this->schemaValidator->assertValid($schema);

        $typeInfo = $typeInfo ?? new TypeInfo($schema);
        $rules    = $rules ?? SupportedRules::build();

        $context = $this->createContext($schema, $document, $typeInfo);

        $visitors = \array_map(function (RuleInterface $rule) use ($context) {
            return $rule->setContext($context);
        }, $rules);

        $visitor = new TypeInfoVisitor($typeInfo, new ParallelVisitor($visitors));

        // Visit the whole document with each instance of all provided rules.
        /** @noinspection PhpUnhandledExceptionInspection */
        $document->acceptVisitor(new VisitorInfo($visitor));

        return $context->getErrors();
    }

    /**
     * @param Schema       $schema
     * @param DocumentNode $document
     * @param TypeInfo     $typeInfo
     * @return ValidationContextInterface
     */
    public function createContext(
        Schema $schema,
        DocumentNode $document,
        TypeInfo $typeInfo
    ): ValidationContextInterface {
        return new ValidationContext($schema, $document, $typeInfo);
    }
}
