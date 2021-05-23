<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Result\Chat\Message;
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

    /** @var Message */
    protected $message;

    /** @var int */
    protected $factualPower = 0;

    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        Message $message
    )
    {
        $this->actionUnit = $actionUnit;
        $this->enemyCommand = $enemyCommand;
        $this->alliesCommand = $alliesCommand;
        $this->message = $message;
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
