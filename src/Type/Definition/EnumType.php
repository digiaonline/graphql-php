<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\EnumTypeDefinitionNode;

/**
 * Enum Type Definition
 * Some leaf values of requests and input values are Enums. GraphQL serializes
 * Enum values as strings, however internally Enums can be represented by any
 * kind of type, often integers.
 * Example:
 *     const RGBType = new GraphQLEnumType({
 *       name: 'RGB',
 *       values: {
 *         RED: { value: 0 },
 *         GREEN: { value: 1 },
 *         BLUE: { value: 2 }
 *       }
 *     });
 * Note: If a value is not provided in a definition, the name of the enum value
 * will be used as its internal value.
 */

/**
 * Class EnumType
 *
 * @package Digia\GraphQL\Type\Definition\Enum
 * @property EnumTypeDefinitionNode $astNode
 */
class EnumType implements TypeInterface, InputTypeInterface, LeafTypeInterface, OutputTypeInterface, TransformInterface
{

    use NameTrait;
    use DescriptionTrait;
    use NodeTrait;
    use ConfigTrait;

    /**
     * @var EnumValue[]
     */
    private $values = [];

    /**
     * @inheritdoc
     */
    public function serialize($value)
    {
        /** @var EnumValue $enumValue */
        $enumValue = $this->getValueByValue($value);

        if ($enumValue) {
            return $enumValue->getName();
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function parseValue($value)
    {
        if (is_string($value)) {
            /** @var EnumValue $enumValue */
            $enumValue = $this->getValueByName($value);

            if ($enumValue) {
                return $enumValue->getValue();
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function parseLiteral(NodeInterface $astNode, ...$args)
    {
        if ($astNode->getKind() === KindEnum::ENUM) {
            /** @var EnumValue $enumValue */
            $enumValue = $this->getValueByName($astNode->getValue());

            if ($enumValue) {
                return $enumValue->getValue();
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @return EnumValue
     */
    public function getValue(string $name): EnumValue
    {
        return $this->getValueByName($name);
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param string $name
     * @return EnumValue|null
     */
    protected function getValueByName(string $name): ?EnumValue
    {
        foreach ($this->values as $val) {
            if ($val->getName() === $name) {
                return $val;
            }
        }

        return null;
    }

    /**
     * @param mixed $value
     * @return EnumValue|null
     */
    protected function getValueByValue($value): ?EnumValue
    {
        foreach ($this->values as $val) {
            if ($val->getValue() === $value) {
                return $val;
            }
        }

        return null;
    }

    /**
     * @param EnumValue $value
     * @return $this
     */
    protected function addValue(EnumValue $value)
    {
        $this->values[] = $value;

        return $this;
    }

    /**
     * @param array $values
     * @return $this
     */
    protected function setValues(array $values)
    {
        foreach ($values as $config) {
            $this->addValue(new EnumValue($config));
        }

        return $this;
    }
}
