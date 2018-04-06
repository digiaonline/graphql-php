<?php

namespace Digia\GraphQL\Language\Writer;

class SupportedWriters
{

    /**
     * @var WriterInterface[]
     */
    private static $writers;

    /**
     * @var array
     */
    private static $supportedWriters = [
        ArgumentWriter::class,
        BooleanValueWriter::class,
        DirectiveWriter::class,
        DocumentWriter::class,
        EnumValueWriter::class,
        FieldWriter::class,
        FloatValueWriter::class,
        FragmentDefinitionWriter::class,
        FragmentSpreadWriter::class,
        InlineFragmentWriter::class,
        IntValueWriter::class,
        ListTypeWriter::class,
        ListValueWriter::class,
        NamedTypeWriter::class,
        NameWriter::class,
        NullTypeWriter::class,
        NullValueWriter::class,
        ObjectFieldWriter::class,
        ObjectValueWriter::class,
        OperationDefinitionWriter::class,
        SelectionSetWriter::class,
        StringValueWriter::class,
        VariableDefinitionWriter::class,
        VariableWriter::class,
        // TODO: Add support for printing Type System Definitions (SDL).
    ];

    /**
     * @return array
     */
    public static function get(): array
    {
        if (null === self::$writers) {
            foreach (self::$supportedWriters as $className) {
                self::$writers[] = new $className();
            }
        }

        return self::$writers;
    }
}
