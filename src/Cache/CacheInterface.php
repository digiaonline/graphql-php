<?php

namespace Digia\GraphQL\Cache;

interface CacheInterface
{

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getItem(string $key);

    /**
     * @param string $key
     * @return bool
     */
    public function hasItem(string $key): bool;

    /**
     * @param string $key
     * @return bool
     */
    public function deleteItem(string $key): bool;

    /**
     * @param string $key
     * @param mixed  $value
     * @return bool
     */
    public function setItem(string $key, $value): bool;
}
