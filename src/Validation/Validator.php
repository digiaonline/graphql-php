<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Visitor\ParallelVisitor;
use Digia\GraphQL\Language\Visitor\TypeInfoVisitor;
use Digia\GraphQL\SchemaValidator\SchemaValidatorInterface;
use Digia\GraphQL\Type\SchemaInterface;
use function Digia\GraphQL\Util\invariant;
use Digia\GraphQL\Util\TypeInfo;
use Digia\GraphQL\Validation\Rule\RuleInterface;
use Digia\GraphQL\Validation\Rule\RulesBuilderInterface;
use Digia\GraphQL\Validation\Rule\SupportedRules;

class Validator implements ValidatorInterface
{
    /**
     * @var ContextBuilderInterface
     */
    protected $contextBuilder;

    /**
     * @var SchemaValidatorInterface
     */
    protected $schemaValidator;

    /**
     * Validator constructor.
     * @param ContextBuilderInterface  $contextBuilder
     * @param SchemaValidatorInterface $schemaValidator
     */
    public function __construct(ContextBuilderInterface $contextBuilder, SchemaValidatorInterface $schemaValidator)
    {
        $this->contextBuilder  = $contextBuilder;
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

        $context = $this->contextBuilder->build($schema, $document, $typeInfo);

        $visitors = \array_map(function (RuleInterface $rule) use ($context) {
            return $rule->setContext($context);
        }, $rules);

        $visitor = new TypeInfoVisitor($typeInfo, new ParallelVisitor($visitors));

        // Visit the whole document with each instance of all provided rules.
        $document->acceptVisitor($visitor);

        return $context->getErrors();
    }
}
