<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Chat\Message;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

class HealAction implements ActionInterface
{
    protected const NAME = 'heal';

    /** @var UnitInterface */
    private $actionUnit;

    /** @var UnitInterface */
    private $targetUnit;

    /** @var CommandInterface */
    private $alliesCommand;

    /** @var int */
    private $factualPower = 0;

    public function __construct(UnitInterface $actionUnit, CommandInterface $alliesCommand)
    {
        $this->actionUnit = $actionUnit;
        $this->alliesCommand = $alliesCommand;
    }

    /**
     * @return string
     */
    public function handle(): string
    {
        $this->targetUnit = $this->alliesCommand->getUnitForHeal();

        if (!$this->targetUnit) {
            return Message::hoTargetForHeal($this);
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
        // Базовое лечение в 120% от силы удара юнита
        return (int)round($this->getActionUnit()->getDamage() * 1.2);
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
}
