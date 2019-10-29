<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\EnumTypeDefinitionNode;
use Digia\GraphQL\Language\Node\EnumValueNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Schema\Definition;
use GraphQL\Contracts\TypeSystem\Type\NamedTypeInterface;
use GraphQL\Contracts\TypeSystem\Type\OutputTypeInterface;
use function Digia\GraphQL\Type\isAssocArray;
use function Digia\GraphQL\Type\newEnumValue;
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
 *     $RGBType = newEnumType([
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
class EnumType extends Definition implements
    NamedTypeInterface,
    InputTypeInterface,
    LeafTypeInterface,
    OutputTypeInterface,
    SerializableTypeInterface,
    DescriptionAwareInterface,
    ASTNodeAwareInterface
{
    use NameTrait;
    use DescriptionTrait;
    use ASTNodeTrait;

    /**
     * @var array
     */
    protected $rawValues;

    /**
     * A list of enum value instances.
     *
     * @var EnumValue[]
     */
    protected $values;

    /**
     * EnumType constructor.
     *
     * @param string                      $name
     * @param null|string                 $description
     * @param EnumValue[]                 $rawValues
     * @param EnumTypeDefinitionNode|null $astNode
     */
    public function __construct(string $name, ?string $description, array $rawValues, ?EnumTypeDefinitionNode $astNode)
    {
        $this->name        = $name;
        $this->description = $description;
        $this->astNode     = $astNode;
        $this->rawValues   = $rawValues;
    }

    /**
     * @param mixed $value
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
     * @param mixed $value
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
            $this->values = $this->buildValues($this->rawValues);
        }
        return $this->values;
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
     * @param array $rawValues
     * @return array
     * @throws InvariantException
     */
    protected function buildValues(array $rawValues): array
    {
        if (!isAssocArray($rawValues)) {
            throw new InvariantException(\sprintf('%s values must be an associative array with value names as keys.',
                $this->name));
        }

        $values = [];

        foreach ($rawValues as $valueName => $valueConfig) {
            if (!isAssocArray($valueConfig)) {
                throw new InvariantException(\sprintf(
                    '%s.%s must refer to an object with a "value" key representing an internal value but got: %s.',
                    $this->name,
                    $valueName,
                    toString($valueConfig)
                ));
            }

            if (isset($valueConfig['isDeprecated'])) {
                throw new InvariantException(\sprintf(
                    '%s.%s should provide "deprecationReason" instead of "isDeprecated".',
                    $this->name,
                    $valueName
                ));
            }

            $valueConfig['name']  = $valueName;
            $valueConfig['value'] = \array_key_exists('value', $valueConfig)
                ? $valueConfig['value']
                : $valueName;

            $values[] = newEnumValue($valueConfig);
        }

        return $values;
    }
}
