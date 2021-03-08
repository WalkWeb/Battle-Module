<?php

declare(strict_types=1);

namespace Battle;

use Battle\Exception\CommandException;

class Command
{
    /**
     * @var Unit[]
     */
    private $units;

    /**
     * Command constructor.
     * @param array $units
     * @throws CommandException
     */
    public function __construct(array $units)
    {
        if (count($units) === 0) {
            throw new CommandException(CommandException::NO_UNITS);
        }

        foreach ($units as $unit) {
            if (!$unit instanceof Unit) {
                throw new CommandException(CommandException::INCORRECT_USER);
            }
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
            if (!$unit->isAction()) {
                return true;
            }
        }

        return false;
    }

    public function getUnitForAttacks(): ?Unit
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

    public function getMeleeUnitForAttacks(): ?Unit
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

    public function getUnitForHeal(): ?Unit
    {
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
     * @return Unit|null
     * @throws CommandException
     */
    public function getUnitForAction(): ?Unit
    {
        if (!$this->isAction() || !$this->isAlive()) {
            return null;
        }

        $actionUnits = [];

        foreach ($this->units as $unit) {
            if ($this->isAlive() && !$unit->isAction()) {
                $actionUnits[] = $unit;
            }
        }

        if (count($actionUnits) === 0) {
            throw new CommandException(CommandException::UNEXPECTED_EVENT_NO_ACTION_UNIT);
        }

        return $actionUnits[array_rand($actionUnits)];
    }

    /**
     * @return Unit[]
     */
    public function getUnits(): array
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
