<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Visitor\ParallelVisitor;
use Digia\GraphQL\Language\Visitor\TypeInfoVisitor;
use Digia\GraphQL\Type\SchemaInterface;
use Digia\GraphQL\Util\TypeInfo;
use Digia\GraphQL\Validation\Rule\RuleInterface;
use Digia\GraphQL\Validation\Rule\RulesBuilderInterface;

class Validator implements ValidatorInterface
{
    /**
     * @var ContextBuilderInterface
     */
    protected $contextBuilder;

    /**
     * @var RulesBuilderInterface
     */
    protected $rulesBuilder;

    /**
     * Validator constructor.
     * @param ContextBuilderInterface $contextBuilder
     * @param RuleInterface[]         $rulesBuilder
     */
    public function __construct(ContextBuilderInterface $contextBuilder, RulesBuilderInterface $rulesBuilder)
    {
        $this->contextBuilder = $contextBuilder;
        $this->rulesBuilder   = $rulesBuilder;
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
        $typeInfo = $typeInfo ?? new TypeInfo($schema);
        $rules    = $rules ?? $this->rulesBuilder->build();

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
