<?php

namespace Digia\GraphQL\Test\Unit\Util;

use Digia\GraphQL\Language\Node\IntValueNode;
use Digia\GraphQL\Language\Node\StringValueNode;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Util\ValueConverter;
use function Digia\GraphQL\Type\ID;

class ValueConverterTest extends TestCase
{

    /**
     * @var ValueConverter
     */
    private $valueConverter;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->valueConverter = new ValueConverter();
    }

    /**
     * @throws \Digia\GraphQL\Error\ConversionException
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testIdType(): void
    {
        // Ensure numerical IDs become Ints
        $this->assertInstanceOf(IntValueNode::class, $this->valueConverter->convert('45', ID()));
        $this->assertInstanceOf(StringValueNode::class, $this->valueConverter->convert('abc123', ID()));
    }
}
