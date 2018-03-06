<?php

namespace Digia\GraphQL\Language\Reader;

class SupportedReaders
{
    /**
     * @var ReaderInterface[]
     */
    private static $readers;

    /**
     * @var array
     */
    private static $supportedReaders = [
        AmpReader::class,
        AtReader::class,
        BangReader::class,
        BlockStringReader::class,
        BraceReader::class,
        BracketReader::class,
        ColonReader::class,
        CommentReader::class,
        DollarReader::class,
        EqualsReader::class,
        NameReader::class,
        NumberReader::class,
        ParenthesisReader::class,
        PipeReader::class,
        SpreadReader::class,
        StringReader::class,
    ];

    /**
     * @return array
     */
    public static function get(): array
    {
        if (null === self::$readers) {
            foreach (self::$supportedReaders as $className) {
                self::$readers[] = new $className();
            }
        }

        return self::$readers;
    }
}
