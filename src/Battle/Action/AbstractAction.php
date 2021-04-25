<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

abstract class AbstractAction implements ActionInterface
{
    /** @var UnitInterface */
    protected $actionUnit;

    /** @var UnitInterface */
    protected $targetUnit;

    /** @var CommandInterface */
    protected $alliesCommand;

    /** @var CommandInterface */
    protected $enemyCommand;

    /** @var int */
    protected $factualPower = 0;

    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    )
    {
        $this->actionUnit = $actionUnit;
        $this->enemyCommand = $enemyCommand;
        $this->alliesCommand = $alliesCommand;
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
}
