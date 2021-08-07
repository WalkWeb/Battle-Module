<?php

declare(strict_types=1);

namespace Battle\Action;

use Exception;
use Battle\Command\CommandInterface;
use Battle\Result\Chat\Message\MessageInterface;
use Battle\Unit\UnitInterface;

class SummonAction extends AbstractAction
{
    private const HANDLE_METHOD = 'applySummonAction';

    /**
     * @var string
     */
    private $name;

    /**
     * @var UnitInterface
     */
    private $summon;

    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        MessageInterface $message,
        string $name,
        UnitInterface $summon
    )
    {
        parent::__construct($actionUnit, $enemyCommand, $alliesCommand, $message);
        $this->name = $name;
        $this->summon = $summon;
    }

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function handle(): string
    {
        $this->alliesCommand->getUnits()->add($this->summon);
        return $this->actionUnit->applyAction($this);
    }

    /**
     * @param int $factualPower
     * @return int|mixed
     */
    public function setFactualPower(int $factualPower): void {}

    /**
     * @return string
     */
    public function getNameAction(): string
    {
        return $this->name;
    }

    /**
     * @return UnitInterface
     */
    public function getSummonUnit(): UnitInterface
    {
        return $this->summon;
    }
}
