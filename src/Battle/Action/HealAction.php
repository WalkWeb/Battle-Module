<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Chat\Message;
use Battle\Command;
use Battle\Exception\UserException;
use Battle\Unit;

class HealAction implements ActionInterface
{
    protected const NAME = 'heal';

    /** @var Unit */
    private $actionUnit;

    /** @var Unit */
    private $targetUnit;

    /** @var Command */
    private $alliesCommand;

    /** @var int */
    private $factualPower = 0;

    public function __construct(Unit $actionUnit, Command $alliesCommand)
    {
        $this->actionUnit = $actionUnit;
        $this->alliesCommand = $alliesCommand;
    }

    /**
     * @return string
     * @throws UserException
     */
    public function handle(): string
    {
        $this->targetUnit = $this->alliesCommand->getUnitForHeal();

        if (!$this->targetUnit) {
            return Message::hoTargetForHeal($this);
        }

        return $this->targetUnit->applyAction($this);
    }

    public function getActionUnit(): Unit
    {
        return $this->actionUnit;
    }

    public function getTargetUnit(): Unit
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
