<?php

namespace Digia\GraphQL\Schema;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Error\PrintException;
use Digia\GraphQL\Type\Definition\Argument;
use Digia\GraphQL\Type\Definition\DeprecationAwareInterface;
use Digia\GraphQL\Type\Definition\DescriptionAwareInterface;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\EnumValue;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\InputField;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InputValueInterface;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\UnionType;
use function Digia\GraphQL\printNode;
use function Digia\GraphQL\Type\isIntrospectionType;
use function Digia\GraphQL\Type\isSpecifiedScalarType;
use function Digia\GraphQL\Type\String;
use function Digia\GraphQL\Util\arrayEvery;
use function Digia\GraphQL\Util\astFromValue;
use function Digia\GraphQL\Util\toString;

class DefinitionPrinter implements DefinitionPrinterInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @inheritdoc
     * @throws PrintException
     * @throws InvariantException
     */
    public function printSchema(Schema $schema, array $options = []): string
    {
        $this->options = $options;

        return $this->printFilteredSchema(
            $schema,
            function (Directive $directive): bool {
                return !isSpecifiedDirective($directive);
            },
            function (TypeInterface $type): bool {
                return !isSpecifiedScalarType($type) && !isIntrospectionType($type);
            }
        );
    }

    /**
     * @inheritdoc
     * @throws PrintException
     * @throws InvariantException
     */
    public function printIntrospectionSchema(Schema $schema, array $options = []): string
    {
        $this->options = $options;

        return $this->printFilteredSchema(
            $schema,
            function (Directive $directive): bool {
                return isSpecifiedDirective($directive);
            },
            function (TypeInterface $type): bool {
                return isIntrospectionType($type);
            }
        );
    }

    /**
     * @param DefinitionInterface $definition
     * @return string
     * @throws PrintException
     * @throws InvariantException
     */
    public function print(DefinitionInterface $definition): string
    {
        if ($definition instanceof Schema) {
            return $this->printSchemaDefinition($definition);
        }
        if ($definition instanceof NamedTypeInterface) {
            return $this->printType($definition);
        }

        throw new PrintException(\sprintf('Invalid definition object: %s.', toString($definition)));
    }

    /**
     * @param Schema   $schema
     * @param callable $directiveFilter
     * @param callable $typeFilter
     * @return string
     * @throws PrintException
     * @throws InvariantException
     */
    protected function printFilteredSchema(
        Schema $schema,
        callable $directiveFilter,
        callable $typeFilter
    ): string {
        /** @noinspection PhpParamsInspection */
        $lines = \array_filter(\array_merge(
            [$this->printOne($schema)],
            $this->printMany($this->getSchemaDirectives($schema, $directiveFilter)),
            $this->printMany($this->getSchemaTypes($schema, $typeFilter))
        ));

        return printArray("\n\n", $lines) . "\n";
    }

    /**
     * @param Schema   $schema
     * @param callable $filter
     * @return array
     */
    protected function getSchemaDirectives(Schema $schema, callable $filter): array
    {
        return \array_filter($schema->getDirectives(), $filter);
    }

    /**
     * @param Schema   $schema
     * @param callable $filter
     * @return array
     */
    protected function getSchemaTypes(Schema $schema, callable $filter): array
    {
        $types = \array_filter(\array_values($schema->getTypeMap()), $filter);

        \usort($types, function (NamedTypeInterface $typeA, NamedTypeInterface $typeB) {
            return \strcasecmp($typeA->getName(), $typeB->getName());
        });

        return $types;
    }

    /**
     * @param Schema $definition
     * @return string
     */
    protected function printSchemaDefinition(Schema $definition): string
    {
        if ($this->isSchemaOfCommonNames($definition)) {
            return '';
        }

        $operationTypes = [];

        if (null !== ($queryType = $definition->getQueryType())) {
            $operationTypes[] = "  query: {$queryType->getName()}";
        }

        if (null !== ($mutationType = $definition->getMutationType())) {
            $operationTypes[] = "  mutation: {$mutationType->getName()}";
        }

        if (null !== ($subscriptionType = $definition->getSubscriptionType())) {
            $operationTypes[] = "  subscription: {$subscriptionType->getName()}";
        }

        return printLines([
            'schema {',
            printLines($operationTypes),
            '}'
        ]);
    }

    /**
     * GraphQL schema define root types for each type of operation. These types are
     * the same as any other type and can be named in any manner, however there is
     * a common naming convention:
     *
     *   schema {
     *     query: Query
     *     mutation: Mutation
     *     subscription: Subscription
     *   }
     *
     * When using this naming convention, the schema description can be omitted.
     *
     * @param Schema $schema
     * @return bool
     */
    protected function isSchemaOfCommonNames(Schema $schema): bool
    {
        if (null !== ($queryType = $schema->getQueryType()) &&
            $queryType->getName() !== 'Query') {
            return false;
        }

        if (null !== ($mutationType = $schema->getMutationType()) &&
            $mutationType->getName() !== 'Mutation') {
            return false;
        }

        if (null !== ($subscriptionType = $schema->getSubscriptionType()) &&
            $subscriptionType->getName() !== 'Subscription') {
            return false;
        }

        return true;
    }

    /**
     * @param NamedTypeInterface $type
     * @return string
     * @throws PrintException
     * @throws InvariantException
     */
    protected function printType(NamedTypeInterface $type): string
    {
        if ($type instanceof ScalarType) {
            return $this->printScalarType($type);
        }
        if ($type instanceof ObjectType) {
            return $this->printObjectType($type);
        }
        if ($type instanceof InterfaceType) {
            return $this->printInterfaceType($type);
        }
        if ($type instanceof UnionType) {
            return $this->printUnionType($type);
        }
        if ($type instanceof EnumType) {
            return $this->printEnumType($type);
        }
        if ($type instanceof InputObjectType) {
            return $this->printInputObjectType($type);
        }

        throw new PrintException(\sprintf('Unknown type: %s', (string)$type));
    }

    /**
     * @param ScalarType $type
     * @return string
     */
    protected function printScalarType(ScalarType $type): string
    {
        return printLines([
            $this->printDescription($type),
            "scalar {$type->getName()}"
        ]);
    }

    /**
     * @param ObjectType $type
     * @return string
     * @throws InvariantException
     */
    protected function printObjectType(ObjectType $type): string
    {
        $description = $this->printDescription($type);
        $name        = $type->getName();
        $implements  = $type->hasInterfaces()
            ? ' implements ' . printArray(' & ', \array_map(function (InterfaceType $interface) {
                return $interface->getName();
            }, $type->getInterfaces()))
            : '';
        $fields      = $this->printFields($type->getFields());

        return printLines([
            $description,
            "type {$name}{$implements} {",
            $fields,
            '}'
        ]);
    }

    /**
     * @param InterfaceType $type
     * @return string
     * @throws InvariantException
     */
    protected function printInterfaceType(InterfaceType $type): string
    {
        $description = $this->printDescription($type);
        $fields      = $this->printFields($type->getFields());

        return printLines([
            $description,
            "interface {$type->getName()} {",
            $fields,
            '}'
        ]);
    }

    /**
     * @param UnionType $type
     * @return string
     * @throws InvariantException
     */
    protected function printUnionType(UnionType $type): string
    {
        $description = $this->printDescription($type);
        $types       = printArray(' | ', $type->getTypes());

        return printLines([
            $description,
            "union {$type->getName()} = {$types}"
        ]);
    }

    /**
     * @param EnumType $type
     * @return string
     * @throws InvariantException
     */
    protected function printEnumType(EnumType $type): string
    {
        $description = $this->printDescription($type);
        $values      = $this->printEnumValues($type->getValues());

        return printLines([
            $description,
            "enum {$type->getName()} {",
            $values,
            '}'
        ]);
    }

    protected function printEnumValues(array $values): string
    {
        return printLines(\array_map(function (EnumValue $value): string {
            $description = $this->printDescription($value, '  ');
            $name        = $value->getName();
            $deprecated  = $this->printDeprecated($value);
            $enum        = empty($deprecated) ? $name : "{$name} {$deprecated}";

            return printLines([
                $description,
                "  {$enum}"
            ]);
        }, $values));
    }

    /**
     * @param InputObjectType $type
     * @return string
     * @throws InvariantException
     */
    protected function printInputObjectType(InputObjectType $type): string
    {
        $description = $this->printDescription($type);
        $fields      = \array_map(function (InputField $field): string {
            $description = $this->printDescription($field, '  ');
            $inputValue  = $this->printInputValue($field);
            return printLines([
                $description,
                "  {$inputValue}"
            ]);
        }, \array_values($type->getFields()));

        return printLines([
            $description,
            "input {$type->getName()} {",
            printLines($fields),
            '}'
        ]);
    }

    /**
     * @param InputValueInterface $inputValue
     * @return string
     */
    protected function printInputValue(InputValueInterface $inputValue): string
    {
        $type         = $inputValue->getType();
        $name         = $inputValue->getName();
        $defaultValue = printNode(astFromValue($inputValue->getDefaultValue(), $type));

        return $inputValue->hasDefaultValue()
            ? "{$name}: {$type} = {$defaultValue}"
            : "{$name}: {$type}";
    }

    /**
     * @param array $fields
     * @return string
     */
    protected function printFields(array $fields): string
    {
        return printLines(\array_map(function (Field $field): string {
            $description = $this->printDescription($field);
            $name        = $field->getName();
            $arguments   = $this->printArguments($field->getArguments());
            $type        = (string)$field->getType();
            $deprecated  = $this->printDeprecated($field);
            return printLines([
                $description,
                "  {$name}{$arguments}: {$type}{$deprecated}"
            ]);
        }, \array_values($fields)));
    }

    protected function printArguments(array $arguments, string $indentation = ''): string
    {
        if (empty($arguments)) {
            return '';
        }

        // If every arg does not have a description, print them on one line.
        if (arrayEvery($arguments, function (Argument $argument): bool {
            return !$argument->hasDescription();
        })) {
            return printInputFields(\array_map(function (Argument $argument) {
                return $this->printInputValue($argument);
            }, $arguments));
        }

        $args = \array_map(function (Argument $argument) use ($indentation) {
            $description = $this->printDescription($argument);
            $inputValue  = $this->printInputValue($argument);
            return printLines([
                $description,
                "  {$indentation}{$inputValue}"
            ]);
        }, $arguments);

        return printLines([
            '(',
            $args,
            $indentation . ')'
        ]);
    }

    /**
     * @param DeprecationAwareInterface $fieldOrEnumValue
     * @return string
     */
    protected function printDeprecated(DeprecationAwareInterface $fieldOrEnumValue): string
    {
        if (!$fieldOrEnumValue->isDeprecated()) {
            return '';
        }

        $reason = $fieldOrEnumValue->getDeprecationReason();

        if (null === $reason || '' === $reason || DEFAULT_DEPRECATION_REASON === $reason) {
            return '@deprecated';
        }

        $reasonValue = printNode(astFromValue($reason, String()));

        return "@deprecated(reason: {$reasonValue})";
    }

    /**
     * @param mixed  $type
     * @param string $indentation
     * @param bool   $isFirstInBlock
     * @return string
     */
    protected function printDescription(
        $type,
        string $indentation = '',
        bool $isFirstInBlock = true
    ): string {
        if (!($type instanceof DescriptionAwareInterface)) {
            return '';
        }

        // Don't print anything if the type has no description
        if ($type->getDescription() === null) {
            return '';
        }

        $lines      = descriptionLines($type->getDescription(), 120 - \strlen($indentation));
        $linesCount = \count($lines);

        if (isset($this->options['commentDescriptions']) && true === $this->options['commentDescriptions']) {
            return $this->printDescriptionWithComments($lines, $indentation, $isFirstInBlock);
        }

        $description = $indentation && !$isFirstInBlock
            ? "\n" . $indentation . '"""'
            : $indentation . '"""';

        // In some circumstances, a single line can be used for the description.
        if (
            $linesCount === 1 &&
            ($firstLineLength = \strlen($lines[0])) < 70 &&
            $lines[0][$firstLineLength - 1] !== '"'
        ) {
            return $description . escapeQuote($lines[0]) . '"""';
        }

        // Format a multi-line block quote to account for leading space.
        $hasLeadingSpace = $lines[0][0] === ' ' || $lines[0][0] === "\t";
        if (!$hasLeadingSpace) {
            $description .= "\n";
        }

        for ($i = 0; $i < $linesCount; $i++) {
            $description .= $i !== 0 || !$hasLeadingSpace
                ? $description .= $indentation
                : escapeQuote($lines[$i]) . "\n";
        }

        $description .= $indentation . '"""';

        return $description;
    }

    protected function printDescriptionWithComments(array $lines, string $indentation, bool $isFirstInBlock): string
    {
        $description = $indentation && !$isFirstInBlock ? "\n" : '';
        $linesCount  = \count($lines);

        for ($i = 0; $i < $linesCount; $i++) {
            $description .= $lines[$i] === ''
                ? $indentation . '#' . "\n"
                : $indentation . '# ' . $lines[$i] . "\n";
        }

        return $description;
    }

    /**
     * @param DefinitionInterface $definition
     * @return string
     * @throws PrintException
     * @throws InvariantException
     */
    protected function printOne(DefinitionInterface $definition): string
    {
        return $this->print($definition);
    }

    /**
     * @param DefinitionInterface[] $definitions
     * @return array
     */
    protected function printMany(array $definitions): array
    {
        return \array_map(function ($definition) {
            return $this->print($definition);
        }, $definitions);
    }
}
