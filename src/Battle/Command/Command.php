<?php

declare(strict_types=1);

namespace Battle\Command;

use Battle\Unit\UnitCollection;
use Battle\Unit\UnitException;
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

    /**
     * Возвращает самого раненого живого юнита в команде, если все живы или мертвы - возвращает null
     *
     * @return UnitInterface|null
     */
    public function getUnitForHeal(): ?UnitInterface
    {
        $unitForHeal = [];

        foreach ($this->units as $unit) {
            $life = $unit->getLife();
            $totalLife = $unit->getTotalLife();
            if ($life > 0 && $life < $totalLife) {
                $percentLife = (int)(($life / $totalLife) * 100);
                $unitForHeal[$percentLife] = $unit;
            }
        }

        if (count($unitForHeal) === 0) {
            return null;
        }

        ksort($unitForHeal);

        return $unitForHeal[array_key_first($unitForHeal)];
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

    public function getMeleeUnits(): UnitCollection
    {
        $collection = new UnitCollection();

        foreach ($this->units as $unit) {
            if ($unit->isMelee()) {
                $collection->add($unit);
            }
        }

        return $collection;
    }

    public function getRangeUnits(): UnitCollection
    {
        $collection = new UnitCollection();

        foreach ($this->units as $unit) {
            if (!$unit->isMelee()) {
                $collection->add($unit);
            }
        }

        return $collection;
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

    public function getTotalLife(): int
    {
        $totalLife = 0;

        foreach ($this->units as $unit) {
            $totalLife += $unit->getLife();
        }

        return $totalLife;
    }

    /**
     * @return mixed|void
     * @throws UnitException
     */
    public function __clone()
    {
        $collection = new UnitCollection();

        foreach ($this->getUnits() as $unit) {
            $collection->add(clone $unit);
        }

        $this->units = $collection;
    }
}
