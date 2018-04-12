<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ASTNodeAwareInterface;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\EnumTypeDefinitionNode;
use Digia\GraphQL\Language\Node\EnumValueNode;
use Digia\GraphQL\Language\Node\NodeInterface;
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
class EnumType implements TypeInterface, NamedTypeInterface, InputTypeInterface, LeafTypeInterface,
    OutputTypeInterface, ASTNodeAwareInterface
{
    use NameTrait;
    use DescriptionTrait;
    use ASTNodeTrait;

    /**
     * Values can be defined either as an array or as a thunk.
     * Using thunks allows for cross-referencing of values.
     *
     * @var array
     */
    protected $valueMap;

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
     * @param EnumValue[]                 $values
     * @param EnumTypeDefinitionNode|null $astNode
     * @throws InvariantException
     */
    public function __construct(string $name, ?string $description, array $values, ?EnumTypeDefinitionNode $astNode)
    {
        $this->name        = $name;
        $this->description = $description;
        $this->astNode     = $astNode;
        $this->valueMap    = $values;

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
            $this->values = $this->buildValues($this->valueMap);
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

            $values[] = new EnumValue(
                $valueName,
                $valueConfig['description'] ?? null,
                $valueConfig['deprecationReason'] ?? null,
                $valueConfig['astNode'] ?? null,
                $valueConfig['value'] ?? null
            );
        }

        return $values;
    }
}
