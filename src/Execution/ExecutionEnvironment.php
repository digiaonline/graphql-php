<?php

namespace Digia\GraphQL\Execution;

class ExecutionEnvironment
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var mixed
     */
    protected $context;

    /**
     * @var ResolveInfo
     */
    protected $info;

    /**
     * ExecutionEnvironment constructor.
     * @param mixed       $value
     * @param array       $arguments
     * @param mixed       $context
     * @param ResolveInfo $info
     */
    public function __construct($value, array $arguments, $context, ResolveInfo $info)
    {
        $this->value     = $value;
        $this->arguments = $arguments;
        $this->context   = $context;
        $this->info      = $info;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return ResolveInfo
     */
    public function getInfo(): ResolveInfo
    {
        return $this->info;
    }
}
