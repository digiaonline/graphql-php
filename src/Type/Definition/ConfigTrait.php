<?php

namespace Digia\GraphQL\Type\Definition;

trait ConfigTrait
{

    /**
     * @var array
     */
    private $config;

    /**
     * AbstractScalarType constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $config = array_merge($this->configure(), $config);

        $this->applyConfig($config);

        $this->setConfig($config);
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return array
     */
    protected function configure(): array
    {
        return [];
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getConfigValue(string $key)
    {
        return $this->config[$key] ?? null;
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function hasConfigValue(string $key): bool
    {
        return isset($this->config[$key]);
    }

    /**
     * @param array $config
     */
    protected function applyConfig(array $config): void
    {
        foreach ($config as $key => $value) {
            $setter = 'set' . ucfirst($key);

            if (method_exists($this, $setter)) {
                $this->$setter($value);
            } elseif (property_exists($this, $key)) {
                $this->$key = $value;
            }
            // TODO: Should we throw an exception here?
        }
    }

    /**
     * @param array $config
     */
    protected function setConfig(array $config): void
    {
        $this->config = $config;
    }
}
