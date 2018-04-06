<?php

namespace Digia\GraphQL\Cache;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

trait CacheAwareTrait
{

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @param mixed $key
     * @param mixed|null $default
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getFromCache($key, $default = null)
    {
        return $this->cache->get($key, $default);
    }

    /**
     * @param mixed $key
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function isInCache($key): bool
    {
        return $this->cache->has($key);
    }

    /**
     * @param mixed $key
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function deleteFromCache($key): bool
    {
        return $this->cache->delete($key);
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @param null $ttl
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function setInCache($key, $value, $ttl = null): bool
    {
        return $this->cache->set($key, $value, $ttl);
    }

    /**
     *
     */
    public function clearCache()
    {
        $this->cache->clear();
    }

    /**
     * @return string
     */
    abstract protected function getCachePrefix(): string;

    /**
     * @param mixed $key
     *
     * @return string
     */
    protected function getCacheKey($key): string
    {
        return $this->getCachePrefix().$key;
    }
}
