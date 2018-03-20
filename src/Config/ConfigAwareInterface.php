<?php

namespace Digia\GraphQL\Config;

interface ConfigAwareInterface
{
    /**
     * @param string     $key
     * @param mixed|null $default
     * @return mixed|null
     */
    public function getConfigValue(string $key, $default = null);

    /**
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setConfigValue(string $key, $value);
}
