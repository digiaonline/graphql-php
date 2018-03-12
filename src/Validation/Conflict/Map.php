<?php

namespace Digia\GraphQL\Validation\Conflict;

class Map
{
    /**
     * @var array
     */
    protected $keys = [];

    /**
     * @var array
     */
    protected $values = [];

    /**
     * Map constructor.
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        foreach ($values as $key => $value) {
            $this->keys[]   = $key;
            $this->values[] = $value;
        }
    }


    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value): void
    {
        $this->keys[]   = $key;
        $this->values[] = $value;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        $index = array_search($key, $this->keys, true);

        if ($index === false) {
            return null;
        }

        return $this->values[$index] ?? null;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key): bool
    {
        return null !== $this->get($key);
    }

    /**
     * @param $key
     */
    public function delete($key): void
    {
        $index = array_search($key, $this->keys);

        array_splice($this->keys, $index, 1);
        array_splice($this->values, $index, 1);
    }

    /**
     * @return array
     */
    public function keys(): array
    {
        return $this->keys;
    }

    /**
     * @return array
     */
    public function values(): array
    {
        return $this->values;
    }

    /**
     *
     */
    public function clear(): void
    {
        $this->keys   = [];
        $this->values = [];
    }
}
