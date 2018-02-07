<?php

namespace Digia\GraphQL\Type\Definition;

trait DeprecationReasonTrait
{

    /**
     * @var ?string
     */
    private $deprecationReason;

    /**
     * @return null|string
     */
    public function getDeprecationReason(): ?string
    {
        return $this->deprecationReason;
    }

    /**
     * @param null|string $deprecationReason
     */
    protected function setDeprecationReason(?string $deprecationReason): void
    {
        if (!$deprecationReason && method_exists($this, 'setIsDeprecated')) {
            $this->setIsDeprecated(true);
        }

        $this->deprecationReason = $deprecationReason;
    }
}
