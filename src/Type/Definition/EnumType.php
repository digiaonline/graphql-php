<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Config\ConfigObject;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\EnumValueNode;
use Digia\GraphQL\Language\Node\NodeAwareInterface;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeTrait;
use Digia\GraphQL\Type\Coercer\CoercerInterface;
use function Digia\GraphQL\Type\isAssocArray;
use function Digia\GraphQL\Util\invariant;
use function Digia\GraphQL\Util\toString;

/**
 * Enum Type Definition
 *
 * Some leaf values of requests and input values are Enums. GraphQL serializes
 * Enum values as strings, however internally Enums can be represented by any
 * kind of type, often integers.
 *
 * Example:
 *
 *     $RGBType = new GraphQLEnumType([
 *       'name'   => 'RGB',
 *       'values' => [
 *         'RED'   => ['value' => 0],
 *         'GREEN' => ['value' => 1],
 *         'BLUE'  => ['value' => 2]
 *       ]
 *     ]);
 *
 * Note: If a value is not provided in a definition, the name of the enum value
 * will be used as its internal value.
 */
class EnumType extends ConfigObject implements TypeInterface, NamedTypeInterface, InputTypeInterface,
    LeafTypeInterface, OutputTypeInterface, NodeAwareInterface, CoercerInterface
{
    use NameTrait;
    use DescriptionTrait;
    use NodeTrait;

    /**
     * @var array
     */
    protected $valueMap;

    /**
     * @var EnumValue[]
     */
    protected $values;

    /**
     * @inheritdoc
     */
    protected function afterConfig(): void
    {
        invariant(null !== $this->getName(), 'Must provide name.');
    }

    /**
     * @param $value
     * @return null|string
     * @throws InvariantException
     */
    public function serialize($value)
    {
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
        if (\is_string($value)) {
            $enumValue = $this->getValueByName($value);

            if ($enumValue !== null) {
                return $enumValue->getValue();
            }
        }

        return null;
    }

    /**
     * @param NodeInterface $node
     * @return mixed|null
     * @throws InvariantException
     */
    public function parseLiteral(NodeInterface $node)
    {
        if ($node instanceof EnumValueNode) {
            $enumValue = $this->getValueByName($node->getValue());

            if ($enumValue !== null) {
                return $enumValue->getValue();
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @return EnumValue|null
     * @throws InvariantException
     */
    public function getValue(string $name): ?EnumValue
    {
        return $this->getValueByName($name);
    }

    /**
     * @return EnumValue[]
     * @throws InvariantException
     */
    public function getValues(): array
    {
        if (!isset($this->values)) {
            $this->values = $this->buildValues($this->valueMap ?? []);
        }
        return $this->values;
    }

    /**
     * @param array $valueMap
     * @return $this
     */
    protected function setValues(array $valueMap): EnumType
    {
        $this->valueMap = $valueMap;
        return $this;
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
     * @return array
     * @throws InvariantException
     */
    protected function buildValues(array $valueMap): array
    {
        invariant(
            isAssocArray($valueMap),
            \sprintf('%s values must be an associative array with value names as keys.', $this->getName())
        );

        $values = [];

        foreach ($valueMap as $valueName => $valueConfig) {
            invariant(
                isAssocArray($valueConfig),
                \sprintf(
                    '%s.%s must refer to an object with a "value" key representing an internal value but got: %s',
                    $this->getName(),
                    $valueName,
                    toString($valueConfig)
                )
            );

            invariant(
                !isset($valueConfig['isDeprecated']),
                \sprintf(
                    '%s.%s should provided "deprecationReason" instead of "isDeprecated".',
                    $this->getName(),
                    $valueName
                )
            );

            $values[] = new EnumValue(\array_merge($valueConfig, ['name' => $valueName]));
        }

        return $values;
    }
}
