<?php

namespace Digia\GraphQL\Config;

trait ConfigAwareTrait
{

    /**
     * @var array
     */
    private $config;

    /**
     * ConfigAwareTrait constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->beforeConfig();
        $this->setConfig($config);
        $this->afterConfig();
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    protected function setConfig(array $config)
    {
        foreach ($config as $key => $value) {
            $setter = 'set'.ucfirst($key);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            } elseif (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        $this->config = $config;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function getConfigValue(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    public function setConfigValue(string $key, $value)
    {
        $this->config[$key] = $value;

        return $this;
    }

    /**
     * Override this method to perform logic BEFORE configuration is applied.
     * This method is useful for setting default values for properties.
     * However, remember to call the parent implementation if you do.
     */
    protected function beforeConfig(): void
    {
    }

    /**
     * Override this method to perform logic AFTER configuration is applied.
     * This method is useful for configuring classes after instantiation,
     * e.g. adding a query type to a schema.
     * However, remember to call the parent implementation if you do.
     */
    protected function afterConfig(): void
    {
    }
}
