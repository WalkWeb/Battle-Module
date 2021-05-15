<?php

declare(strict_types=1);

namespace Battle;

use Battle\Result\Chat\FullLog;
use Battle\Command\CommandInterface;
use Battle\Round\RoundException;
use Battle\Round\RoundFactory;
use Battle\Statistic\Statistic;
use Exception;
use Battle\Result\ResultException;
use Battle\Result\Result;
use Battle\Result\ResultInterface;

class Battle implements BattleInterface
{
    /** @var CommandInterface */
    private $leftCommand;

    /** @var CommandInterface */
    private $rightCommand;

    /** @var int - Команда, которая совершает ход: 1 - leftCommand, 2 - rightCommand */
    private $actionCommand;

    /** @var int */
    private $maxRound = 100;

    /** @var bool */
    private $debug;

    /** @var Statistic */
    private $statistics;

    /** @var FullLog */
    private $chat;

    /** @var RoundFactory */
    private $roundFactory;

    /**
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param Statistic $statistics
     * @param FullLog $chat
     * @param bool|null $debug
     * @param RoundFactory|null $roundFactory
     * @throws Exception
     */
    public function __construct(
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        Statistic $statistics,
        FullLog $chat,
        ?bool $debug = true,
        ?RoundFactory $roundFactory = null
    )
    {
        $this->checkDoubleUnitId($leftCommand, $rightCommand);
        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
        $this->statistics = $statistics;
        $this->chat = $chat;
        $this->actionCommand = random_int(1, 2);
        $this->debug = $debug;
        $this->roundFactory = $roundFactory ?? new RoundFactory();
    }

    /**
     * Обрабатывает бой, возвращая результат выполнения
     *
     * @return ResultInterface
     * @throws BattleException
     * @throws ResultException
     * @throws RoundException
     */
    public function handle(): ResultInterface
    {
        $i = 0;

        while ($i < $this->maxRound) {
            $round = $this->roundFactory->create(
                $this->leftCommand,
                $this->rightCommand,
                $this->actionCommand,
                $this->statistics,
                $this->chat,
                $this->debug
            );

            // Выполняем раунд, получая номер команды, которая будет ходить следующей
            $this->actionCommand = $round->handle();

            // Проверяем живых в командах
            if (!$this->leftCommand->isAlive() || !$this->rightCommand->isAlive()) {
                $winner = !$this->leftCommand->isAlive() ? 2 : 1;
                return new Result($this->leftCommand, $this->rightCommand, $winner, $this->chat, $this->statistics);
            }

            $this->statistics->increasedRound();
            $i++;
        }

        throw new BattleException(BattleException::UNEXPECTED_ENDING_BATTLE);
    }

    /**
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @throws BattleException
     */
    private function checkDoubleUnitId(CommandInterface $leftCommand, CommandInterface $rightCommand): void
    {
        $ids = [];

        foreach ($leftCommand->getUnits() as $unit) {

            if (in_array($unit->getId(), $ids, true)) {
                throw new BattleException(BattleException::DOUBLE_UNIT_ID);
            }

            $ids[] = $unit->getId();
        }

        foreach ($rightCommand->getUnits() as $unit) {

            if (in_array($unit->getId(), $ids, true)) {
                throw new BattleException(BattleException::DOUBLE_UNIT_ID);
            }

            $ids[] = $unit->getId();
        }
    }
}
