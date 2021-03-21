<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Exception\DamageActionException;
use Battle\Unit\UnitInterface;

class DamageAction extends AbstractAction
{
    protected const NAME = 'normal attack';

    /**
     * @return string
     * @throws DamageActionException
     */
    public function handle(): string
    {
        if (!$this->enemyCommand->isAlive()) {
            throw new DamageActionException(DamageActionException::NO_DEFINED);
        }

        $this->targetUnit = $this->getDefinedUnit();

        if (!$this->targetUnit) {
            throw new DamageActionException(DamageActionException::NO_DEFINED_AGAIN);
        }

        return $this->targetUnit->applyAction($this);
    }

    public function setFactualPower(int $factualPower): void
    {
        $this->factualPower = $factualPower;
    }

    private function getDefinedUnit(): ?UnitInterface
    {
        if ((!$this->actionUnit->isMelee()) || ($this->actionUnit->isMelee() && !$this->enemyCommand->existMeleeUnits())) {
            return $this->enemyCommand->getUnitForAttacks();
        }

        return $this->enemyCommand->getMeleeUnitForAttacks();
    }
}
