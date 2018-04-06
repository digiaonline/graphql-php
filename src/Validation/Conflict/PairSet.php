<?php

namespace Digia\GraphQL\Validation\Conflict;

/**
 * A way to keep track of pairs of things when the ordering of the pair does
 * not matter. We do this by maintaining a sort of double adjacency sets.
 */
class PairSet
{

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param string $a
     * @param string $b
     * @param bool $areMutuallyExclusive
     *
     * @return bool
     */
    public function has(string $a, string $b, bool $areMutuallyExclusive): bool
    {
        $first = $this->data[$a] ?? null;
        $result = (null !== $first && isset($first[$b])) ? $first[$b] : null;

        if (null === $result) {
            return false;
        }

        // areMutuallyExclusive being false is a superset of being true,
        // hence if we want to know if this PairSet "has" these two with no
        // exclusivity, we have to ensure it was added as such.
        if ($areMutuallyExclusive === false) {
            return $result === false;
        }

        return true;
    }

    /**
     * @param string $a
     * @param string $b
     * @param bool $areMutuallyExclusive
     */
    public function add(string $a, string $b, bool $areMutuallyExclusive): void
    {
        $this->addToData($a, $b, $areMutuallyExclusive);
        $this->addToData($b, $a, $areMutuallyExclusive);
    }

    /**
     * @param string $a
     * @param string $b
     * @param bool $areMutuallyExclusive
     */
    protected function addToData(
        string $a,
        string $b,
        bool $areMutuallyExclusive
    ): void {
        $map = $this->data[$a] ?? [];

        $map[$b] = $areMutuallyExclusive;

        $this->data[$a] = $map;
    }
}
