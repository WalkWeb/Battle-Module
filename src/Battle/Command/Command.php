<?php

declare(strict_types=1);

namespace Battle\Command;

use Battle\Unit\UnitCollection;
use Battle\Unit\UnitInterface;

class Command implements CommandInterface
{
    /**
     * @var UnitCollection
     */
    private $units;

    /**
     * @param UnitCollection $units
     * @throws CommandException
     */
    public function __construct(UnitCollection $units)
    {
        if (count($units) === 0) {
            throw new CommandException(CommandException::NO_UNITS);
        }

        $this->units = $units;
    }

    public function isAlive(): bool
    {
        foreach ($this->units as $unit) {
            if ($unit->isAlive()) {
                return true;
            }
        }

        return false;
    }

    public function isAction(): bool
    {
        foreach ($this->units as $unit) {
            if ($unit->isAlive() && !$unit->isAction()) {
                return true;
            }
        }

        return false;
    }

    public function getUnitForAttacks(): ?UnitInterface
    {
        $aliveUnits = [];

        foreach ($this->units as $unit) {
            if ($unit->isAlive()) {
                $aliveUnits[] = $unit;
            }
        }

        if (count($aliveUnits) === 0) {
            return null;
        }

        return $aliveUnits[array_rand($aliveUnits)];
    }

    public function getMeleeUnitForAttacks(): ?UnitInterface
    {
        $meleeAliveUnits = [];

        foreach ($this->units as $unit) {
            if ($unit->isMelee() && $unit->isAlive()) {
                $meleeAliveUnits[] = $unit;
            }
        }

        if (count($meleeAliveUnits) === 0) {
            return null;
        }

        return $meleeAliveUnits[array_rand($meleeAliveUnits)];
    }

    public function getUnitForHeal(): ?UnitInterface
    {
        // TODO Ошибка: выбираются мертвые юниты

        // TODO Также выбирается не самый битый юнит, а случайный из битых

        $unitForHeal = [];

        foreach ($this->units as $unit) {
            if ($unit->getLife() < $unit->getTotalLife()) {
                $unitForHeal[] = $unit;
            }
        }

        if (count($unitForHeal) === 0) {
            return null;
        }

        return $unitForHeal[array_rand($unitForHeal)];
    }

    /**
     * Возвращает случайного юнита, готового совершить действие
     *
     * @return UnitInterface|null
     * @throws CommandException
     */
    public function getUnitForAction(): ?UnitInterface
    {
        if (!$this->isAction() || !$this->isAlive()) {
            return null;
        }

        $actionUnits = [];

        foreach ($this->units as $unit) {
            if ($unit->isAlive() && !$unit->isAction()) {
                $actionUnits[] = $unit;
            }
        }

        if (count($actionUnits) === 0) {
            throw new CommandException(CommandException::UNEXPECTED_EVENT_NO_ACTION_UNIT);
        }

        return $actionUnits[array_rand($actionUnits)];
    }

    /**
     * @return UnitCollection
     */
    public function getUnits(): UnitCollection
    {
        return $this->units;
    }

    public function getMeleeUnits(): array
    {
        $units = [];

        foreach ($this->units as $unit) {
            if ($unit->isMelee()) {
                $units[] = $unit;
            }
        }

        return $units;
    }

    public function getRangeUnits(): array
    {
        $units = [];

        foreach ($this->units as $unit) {
            if (!$unit->isMelee()) {
                $units[] = $unit;
            }
        }

        return $units;
    }

    public function existMeleeUnits(): bool
    {
        foreach ($this->units as $unit) {
            if ($unit->isMelee() && $unit->isAlive()) {
                return true;
            }
        }

        return false;
    }

    public function newRound(): void
    {
        foreach ($this->units as $unit) {
            $unit->newRound();
        }
    }
}
