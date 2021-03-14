<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Exception\DamageActionException;
use Battle\Unit\UnitInterface;

class DamageAction implements ActionInterface
{
    protected const NAME = 'normal attack';

    /** @var UnitInterface */
    private $actionUnit;

    /** @var UnitInterface */
    private $targetUnit;

    /** @var CommandInterface */
    private $enemyCommand;

    /** @var int */
    private $factualPower;

    public function __construct(UnitInterface $actionUnit, CommandInterface $enemyCommand)
    {
        $this->actionUnit = $actionUnit;
        $this->enemyCommand = $enemyCommand;
    }

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

    public function getActionUnit(): UnitInterface
    {
        return $this->actionUnit;
    }

    public function getTargetUnit(): UnitInterface
    {
        return $this->targetUnit;
    }

    public function getPower(): int
    {
        return $this->actionUnit->getDamage();
    }

    public function getFactualPower(): int
    {
        return $this->factualPower;
    }

    public function setFactualPower(int $factualPower): void
    {
        $this->factualPower = $factualPower;
    }

    public function getNameAction(): string
    {
        return static::NAME;
    }

    private function getDefinedUnit(): ?UnitInterface
    {
        if ((!$this->actionUnit->isMelee()) || ($this->actionUnit->isMelee() && !$this->enemyCommand->existMeleeUnits())) {
            return $this->enemyCommand->getUnitForAttacks();
        }

        return $this->enemyCommand->getMeleeUnitForAttacks();
    }
}
