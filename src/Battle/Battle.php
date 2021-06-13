<?php

declare(strict_types=1);

namespace Battle;

use Battle\Result\Chat\Chat;
use Battle\Result\FullLog\FullLog;
use Battle\Command\CommandInterface;
use Battle\Result\Scenario\Scenario;
use Battle\Result\Scenario\ScenarioInterface;
use Battle\Round\RoundException;
use Battle\Round\RoundFactory;
use Battle\Statistic\Statistic;
use Battle\Translation\Translation;
use Battle\Translation\TranslationInterface;
use Battle\Result\ResultException;
use Battle\Result\Result;
use Battle\Result\ResultInterface;
use Exception;

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
    private $fullLog;

    /** @var Chat */
    private $chat;

    /** @var RoundFactory */
    private $roundFactory;

    /** @var Translation */
    private $translation;

    /** @var ScenarioInterface */
    private $scenario;

    /**
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param Statistic $statistics
     * @param FullLog $fullLog
     * @param Chat $chat
     * @param bool|null $debug
     * @param RoundFactory|null $roundFactory
     * @param TranslationInterface|null $translation
     * @param ScenarioInterface|null $scenario
     * @throws BattleException
     * @throws Exception
     */
    public function __construct(
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        Statistic $statistics,
        FullLog $fullLog,
        Chat $chat,
        ?bool $debug = true,
        ?RoundFactory $roundFactory = null,
        ?TranslationInterface $translation = null,
        ?ScenarioInterface $scenario = null
    )
    {
        $this->checkDoubleUnitId($leftCommand, $rightCommand);
        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
        $this->statistics = $statistics;
        $this->fullLog = $fullLog;
        $this->chat = $chat;
        $this->actionCommand = random_int(1, 2);
        $this->debug = $debug;
        $this->roundFactory = $roundFactory ?? new RoundFactory();
        $this->translation = $translation ?? new Translation();
        $this->scenario = $scenario ?? new Scenario();
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

        $startLeftCommand = clone $this->leftCommand;
        $startRightCommand = clone $this->rightCommand;

        while ($i < $this->maxRound) {
            $round = $this->roundFactory->create(
                $this->leftCommand,
                $this->rightCommand,
                $this->actionCommand,
                $this->statistics,
                $this->fullLog,
                $this->chat,
                $this->scenario,
                $this->debug,
                $this->translation
            );

            // Выполняем раунд, получая номер команды, которая будет ходить следующей
            $this->actionCommand = $round->handle();

            // Проверяем живых в командах
            if (!$this->leftCommand->isAlive() || !$this->rightCommand->isAlive()) {
                $winner = !$this->leftCommand->isAlive() ? 2 : 1;
                return new Result(
                    $startLeftCommand,
                    $startRightCommand,
                    $this->leftCommand,
                    $this->rightCommand,
                    $winner,
                    $this->fullLog,
                    $this->chat,
                    $this->scenario,
                    $this->statistics,
                    $this->translation
                );
            }

            $this->statistics->increasedRound();
            $i++;
        }

        throw new BattleException(BattleException::UNEXPECTED_ENDING_BATTLE);
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @return Translation
     */
    public function getTranslation(): Translation
    {
        return $this->translation;
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
