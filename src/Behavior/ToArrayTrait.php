<?php

namespace Digia\GraphQL\Behavior;

trait ToArrayTrait
{
    // TODO: Evaluate if we want this (as it makes testing and debugging much easier).

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        return $this->thisToArray();
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function thisToArray(): array
    {
        $result = [];

        foreach (get_object_vars($this) as $key => $value) {
            // Skip the "config" -property, we do not want to expose it.
            if ($key === 'config') {
                continue;
            }

            $getter = 'get' . ucfirst($key);

            if (method_exists($this, $getter)) {
                $value = $this->$getter();
            }

            if (is_scalar($value)) {
                $result[$key] = $value;
                continue;
            }

            if (is_object($value) && method_exists($value, 'toArray')) {
                // Serialization has to be done in the right scope.
                $result[$key] = $value->toArray();
                continue;
            }

            if (is_array($value)) {
                $result[$key] = $this->objectsToArrays($value);
                continue;
            }
        }

        return $result;
    }

    /**
     * @param array $objects
     * @return array
     */
    private function objectsToArrays(array $objects): array
    {
        $result = [];

        foreach($objects as $key => $object) {
            if (is_object($object) && method_exists($object, 'toArray')) {
                // Serialization has to be done inside the instance to be done in the right scope.
                $result[$key] = $object->toArray();
            }
        }

        return $result;
    }
}
