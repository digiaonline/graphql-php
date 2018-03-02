<?php

namespace Digia\GraphQL\Cache;

use Psr\SimpleCache\CacheInterface;

class RuntimeCache implements CacheInterface
{

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @inheritdoc
     */
    public function get($key, $default = null)
    {
        return $this->cache[$key] ?? $default;
    }

    /**
     * @inheritdoc
     */
    public function has($key): bool
    {
        return isset($this->cache[$key]);
    }

    /**
     * @inheritdoc
     */
    public function delete($key): bool
    {
        unset($this->cache[$key]);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = null): bool
    {
        $this->cache[$key] = $value;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        return $this->cache = [];
    }

    /**
     * @inheritdoc
     */
    public function getMultiple($keys, $default = null)
    {
        return array_filter($this->cache, function ($key) use ($keys) {
            return \in_array($key, $keys, true);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @inheritdoc
     */
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
    }
}
