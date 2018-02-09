<?php

namespace Digia\GraphQL\Type\Definition\Behavior;

use Digia\GraphQL\Type\Definition\Contract\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\TypeInterface;
use Digia\GraphQL\Type\Definition\Field;
use function Digia\GraphQL\Util\instantiateAssocFromArray;
use function Digia\GraphQL\Util\invariant;

trait FieldsTrait
{

    /**
     * @var array|callable|Field[]
     */
    private $fields;

    /**
     * @var Field[]
     */
    private $_fields;

    /**
     * @param string $name
     * @return Field|null
     * @throws \Exception
     */
    public function getField(string $name): ?Field
    {
        $this->defineFields();

        return $this->_fields[$name] ?? null;
    }

    /**
     * @return Field[]
     * @throws \Exception
     */
    public function getFields(): array
    {
        $this->defineFields();

        return $this->_fields;
    }

    /**
     * @param Field $field
     * @return $this
     */
    protected function addField(Field $field)
    {
        $this->_fields[$field->getName()] = $field;

        return $this;
    }

    /**
     * @throws \Exception
     */
    protected function defineFields()
    {
        if ($this->_fields === null) {
            $this->_fields = defineFieldMap($this, $this->fields);
        }
    }
}

/**
 * @param mixed $type
 * @param mixed $fields
 * @return array
 * @throws \Exception
 */
function defineFieldMap($type, $fields): array
{
    if (is_callable($fields)) {
        $fields = $fields();
    }

    invariant(
        is_array($fields),
        sprintf('%s fields must be an array or a callable which returns such an array.', $type->getName())
    );

    return instantiateAssocFromArray(Field::class, $fields);
}
