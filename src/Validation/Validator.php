<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Visitor\ParallelVisitor;
use Digia\GraphQL\Language\Visitor\TypeInfoVisitor;
use Digia\GraphQL\SchemaValidation\SchemaValidatorInterface;
use Digia\GraphQL\Type\SchemaInterface;
use Digia\GraphQL\Util\TypeInfo;
use Digia\GraphQL\Validation\Rule\RuleInterface;
use Digia\GraphQL\Validation\Rule\SupportedRules;
use function Digia\GraphQL\Util\invariant;

class Validator implements ValidatorInterface
{
    /**
     * @var ValidationContextCreatorInterface
     */
    protected $contextCreator;

    /**
     * @var SchemaValidatorInterface
     */
    protected $schemaValidator;

    /**
     * Validator constructor.
     * @param ValidationContextCreatorInterface $contextCreator
     * @param SchemaValidatorInterface          $schemaValidator
     */
    public function __construct(
        ValidationContextCreatorInterface $contextCreator,
        SchemaValidatorInterface $schemaValidator
    ) {
        $this->contextCreator  = $contextCreator;
        $this->schemaValidator = $schemaValidator;
    }

    /**
     * @inheritdoc
     */
    public function validate(
        SchemaInterface $schema,
        DocumentNode $document,
        ?array $rules = null,
        ?TypeInfo $typeInfo = null
    ): array {
        invariant(null !== $document, 'Must provided document');

        $this->schemaValidator->assertValid($schema);

        $typeInfo = $typeInfo ?? new TypeInfo($schema);
        $rules    = $rules ?? SupportedRules::build();

        $context = $this->contextCreator->create($schema, $document, $typeInfo);

        $visitors = \array_map(function (RuleInterface $rule) use ($context) {
            return $rule->setContext($context);
        }, $rules);

        $visitor = new TypeInfoVisitor($typeInfo, new ParallelVisitor($visitors));

        // Visit the whole document with each instance of all provided rules.
        $document->acceptVisitor($visitor);

        return $context->getErrors();
    }
}
