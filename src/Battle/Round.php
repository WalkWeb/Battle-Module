<?php

declare(strict_types=1);

namespace Battle;

use Battle\Exception\RoundException;
use Battle\Exception\CommandException;
use Battle\Statistic\BattleStatistic;
use Battle\Chat\Chat;

class Round
{
    private const END          = 'Battle end';
    private const START_ROUND  = 'Start new round';
    private const END_ROUND    = 'All command actions. New round';
    private const START_STROKE = 'Start Stroke';
    private const END_STROKE   = 'End Stroke';
    private const HR           = '<hr>';

    /** @var Command */
    private $leftCommand;

    /** @var Command */
    private $rightCommand;

    /** @var int - Команда, которая совершает ход: 1 - leftCommand, 2 - rightCommand */
    private $actionCommand;

    /** @var int */
    private $maxStroke = 50;

    /** @var BattleStatistic */
    private $statistics;

    /** @var Chat */
    private $chat;

    /** @var bool */
    private $debug;

    /**
     * @param Command $leftCommand
     * @param Command $rightCommand
     * @param int $actionCommand
     * @param BattleStatistic $statistics
     * @param Chat $chat
     * @param bool $debug
     * @throws RoundException
     */
    public function __construct(
        Command $leftCommand,
        Command $rightCommand,
        int $actionCommand,
        BattleStatistic $statistics,
        Chat $chat,
        bool $debug = false
    )
    {
        if ($actionCommand !== 1 && $actionCommand !== 2) {
            throw new RoundException(RoundException::INCORRECT_START_COMMAND);
        }

        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
        $this->statistics = $statistics;
        $this->chat = $chat;
        $this->actionCommand = $actionCommand;
        $this->debug = $debug;
    }

    /**
     * Выполняет раунд
     *
     * Раундом считается выполненным, когда все живые юниты сделали свой ход. После этого, если обе команды остались
     * живы - сбрасываются параметры $action у юнитов и начинается новый раунд.
     *
     * @return int
     * @throws CommandException
     * @throws Exception\ActionCollectionException
     * @throws RoundException
     */
    public function handle(): int
    {
        $this->startRound();

        $i = 0;
        while ($i < $this->maxStroke) {

            $actionUnit = $this->actionCommand === 1 ? $this->leftCommand->getUnitForAction() : $this->rightCommand->getUnitForAction();

            if ($actionUnit) {

                // Выполняем один ход - т.е. действие одного юнита
                $stroke = new Stroke(
                    $this->actionCommand,
                    $actionUnit,
                    $this->leftCommand,
                    $this->rightCommand,
                    $this->statistics,
                    $this->chat,
                    $this->debug
                );

                $this->executeStroke($stroke);

                // Проверяем живых в командах
                if (!$this->leftCommand->isAlive() || !$this->rightCommand->isAlive()) {
                    return $this->endBattle();
                }

                $this->statistics->increasedStroke();

                // Проверяем, остались ли юниты, которые не ходили
                if (!$this->leftCommand->isAction() && !$this->rightCommand->isAction()) {
                    return $this->endRound();
                }
            }

            $this->actionCommand = $this->actionCommand === 1 ? $this->actionCommand = 2 : 1;
            $i++;
        }

        throw new RoundException(RoundException::UNEXPECTED_ENDING);
    }

    public function getStatistics(): BattleStatistic
    {
        return $this->statistics;
    }

    /**
     * @param Stroke $stroke
     * @throws Exception\ActionCollectionException
     */
    private function executeStroke(Stroke $stroke): void
    {
        $this->chat->add(self::START_STROKE . ' #' . $this->statistics->getStrokeNumber());
        $stroke->handle();
        $this->chat->add(self::END_STROKE . ' #' . $this->statistics->getStrokeNumber());
        $this->chat->add(self::HR);
    }
    
    private function endBattle(): int
    {
        $this->chat->add(self::END);
        return $this->actionCommand;
    }

    private function startRound(): void
    {
        $this->chat->add(self::START_ROUND . ' #' . $this->statistics->getRoundNumber());
    }

    private function endRound(): int
    {
        $this->chat->add(self::END_ROUND);
        $this->leftCommand->newRound();
        $this->rightCommand->newRound();
        return $this->actionCommand;
    }
}
