<?php

declare(strict_types=1);

namespace Battle\Effect\Change;

class ChangeCollection
{
    /**
     * @var Change[]
     */
    private $changes = [];

    public function add(Change $change): void
    {
        $this->changes[] = $change;
    }

    /**
     * @return Change[]
     */
    public function getChanges(): array
    {
        return $this->changes;
    }
}
