<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Config\ConfigObject;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\AST\Node\EnumTypeDefinitionNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use function Digia\GraphQL\Type\isAssocArray;
use function Digia\GraphQL\Util\invariant;
use function Digia\GraphQL\Util\toString;

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
class EnumType extends ConfigObject implements TypeInterface, NamedTypeInterface, InputTypeInterface, LeafTypeInterface, OutputTypeInterface
{

    use NameTrait;
    use DescriptionTrait;
    use NodeTrait;

    /**
     * @var array
     */
    private $_valueMap = [];

    /**
     * @var EnumValue[]
     */
    private $_values = [];

    /**
     * @var bool
     */
    private $_isValuesDefined = false;

    /**
     * @inheritdoc
     */
    protected function beforeConfig(): void
    {
        $this->setName(TypeNameEnum::ENUM);
    }

    /**
     * @param $value
     * @return null|string
     * @throws InvariantException
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
     * @param $value
     * @return mixed|null
     * @throws InvariantException
     */
    public function parseValue($value)
    {
        if (is_string($value)) {
            /** @var EnumValue $enumValue */
            $enumValue = $this->getValueByName($value);

            if ($enumValue !== null) {
                return $enumValue->getValue();
            }
        }

        return null;
    }

    /**
     * @param NodeInterface $astNode
     * @return mixed|null
     * @throws InvariantException
     */
    public function parseLiteral(NodeInterface $astNode)
    {
        if ($astNode->getKind() === NodeKindEnum::ENUM) {
            /** @var EnumValue $enumValue */
            /** @noinspection PhpUndefinedMethodInspection */
            $enumValue = $this->getValueByName($astNode->getValue());

            if ($enumValue !== null) {
                return $enumValue->getValue();
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @return EnumValue
     * @throws InvariantException
     */
    public function getValue(string $name): EnumValue
    {
        return $this->getValueByName($name);
    }

    /**
     * @return EnumValue[]
     * @throws InvariantException
     */
    public function getValues(): array
    {
        $this->defineEnumValuesIfNecessary();

        return $this->_values;
    }

    /**
     * @param string $name
     * @return EnumValue|null
     * @throws InvariantException
     */
    protected function getValueByName(string $name): ?EnumValue
    {
        foreach ($this->getValues() as $enumValue) {
            if ($enumValue->getName() === $name) {
                return $enumValue;
            }
        }

        return null;
    }

    /**
     * @param mixed $value
     * @return EnumValue|null
     * @throws InvariantException
     */
    protected function getValueByValue($value): ?EnumValue
    {
        foreach ($this->getValues() as $enumValue) {
            if ($enumValue->getValue() === $value) {
                return $enumValue;
            }
        }

        return null;
    }

    /**
     * @param array $valueMap
     * @return $this
     */
    protected function setValues(array $valueMap): EnumType
    {
        $this->_valueMap = $valueMap;

        return $this;
    }

    /**
     * @throws InvariantException
     */
    protected function defineEnumValuesIfNecessary(): void
    {
        if (!$this->_isValuesDefined) {
            $this->_values = array_merge($this->defineEnumValues($this->_valueMap), $this->_values);

            $this->_isValuesDefined = true;
        }
    }

    /**
     * @param array $valueMap
     * @return array
     * @throws InvariantException
     */
    protected function defineEnumValues(array $valueMap): array
    {
        invariant(
            isAssocArray($valueMap),
            sprintf('%s values must be an associative array with value names as keys.', $this->getName())
        );

        $values = [];

        foreach ($valueMap as $valueName => $valueConfig) {
            invariant(
                isAssocArray($valueConfig),
                sprintf(
                    '%s.%s must refer to an object with a "value" key representing an internal value but got: %s',
                    $this->getName(),
                    $valueName,
                    toString($valueConfig)
                )
            );

            invariant(
                !isset($valueConfig['isDeprecated']),
                sprintf(
                    '%s.%s should provided "deprecationReason" instead of "isDeprecated".',
                    $this->getName(),
                    $valueName
                )
            );

            $values[] = new EnumValue(array_merge($valueConfig, ['name' => $valueName]));
        }

        return $values;
    }
}
