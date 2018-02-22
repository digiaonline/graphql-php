<?php

namespace Digia\GraphQL\Type\Definition;

use function Digia\GraphQL\Util\toString;


class StringType extends ScalarType
{
    /**
     * StringType constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->setName(TypeEnum::STRING);

        $this->setSerialize(function($value) {
            return toString($value);
        });

        parent::__construct($config);
    }
}
