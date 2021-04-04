<?php

declare(strict_types=1);

namespace Battle\Command;

use Battle\Unit\UnitCollection;
use Battle\Unit\UnitInterface;

interface CommandInterface
{
    public function isAlive(): bool;
    public function getUnitForAttacks(): ?UnitInterface;
    public function getMeleeUnitForAttacks(): ?UnitInterface;
    public function getUnitForAction(): ?UnitInterface;
    public function getUnits(): UnitCollection;
    public function getMeleeUnits(): array;
    public function getRangeUnits(): array;
    public function existMeleeUnits(): bool;
    public function newRound(): void;
}
