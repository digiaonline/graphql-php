<?php

namespace Digia\GraphQL\Schema;

interface DefinitionPrinterInterface
{
    /**
     * Prints a schema.
     *
     * Accepts options as a second argument:
     *
     *    - commentDescriptions:
     *        Provide true to use preceding comments as the description.
     *
     * @param Schema $schema
     * @param array  $options
     * @return string
     */
    public function printSchema(Schema $schema, array $options = []): string;

    /**
     * Prints an introspection schema.
     *
     * Accepts options as a second argument:
     *
     *    - commentDescriptions:
     *        Provide true to use preceding comments as the description.
     *
     * @param Schema $schema
     * @param array  $options
     * @return string
     */
    public function printIntrospectionSchema(Schema $schema, array $options = []): string;

    /**
     * Prints a GraphQL definition.
     *
     * Accepts options as a second argument:
     *
     *    - commentDescriptions:
     *        Provide true to use preceding comments as the description.
     *
     * @param DefinitionInterface $definition
     * @return string
     */
    public function print(DefinitionInterface $definition): string;
}
