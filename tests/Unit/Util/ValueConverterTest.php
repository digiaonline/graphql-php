<?php

namespace Digia\GraphQL\Test\Unit\Util;

use Digia\GraphQL\Language\Node\IntValueNode;
use Digia\GraphQL\Language\Node\StringValueNode;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Util\ValueConverter;
use function Digia\GraphQL\Type\idType;

class ValueConverterTest extends TestCase
{

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Language\SyntaxErrorException
     * @throws \Digia\GraphQL\Util\ConversionException
     */
    public function testIdType(): void
    {
        // Ensure numerical IDs become Ints
        $this->assertInstanceOf(IntValueNode::class, ValueConverter::convert('45', idType()));
        $this->assertInstanceOf(StringValueNode::class, ValueConverter::convert('abc123', idType()));
    }
}
