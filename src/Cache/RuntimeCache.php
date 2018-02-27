<?php

namespace Digia\GraphQL\Cache;

class RuntimeCache implements CacheInterface
{

    /**
     * @var array
     */
    private $_cache = [];

    /**
     * @inheritdoc
     */
    public function getItem(string $key)
    {
        return $this->_cache[$key] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function hasItem(string $key): bool
    {
        return isset($this->_cache[$key]);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function deleteItem(string $key): bool
    {
        unset($this->_cache[$key]);
        return true;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return bool
     */
    public function setItem(string $key, $value): bool
    {
        $this->_cache[$key] = $value;
        return true;
    }

    /**
     * @return bool
     */
    public function commit(): bool
    {
        return true;
    }
}
