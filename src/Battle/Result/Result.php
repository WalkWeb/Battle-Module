<?php

declare(strict_types=1);

namespace Battle\Result;

use Battle\Result\Chat\Chat;
use Battle\Result\FullLog\FullLog;
use Battle\Command\CommandInterface;
use Battle\Result\Scenario\ScenarioInterface;
use Battle\Statistic\Statistic;
use Battle\Translation\Translation;
use Battle\Translation\TranslationInterface;

class Result implements ResultInterface
{
    /**
     * @var CommandInterface
     */
    private $startLeftCommand;

    /**
     * @var CommandInterface
     */
    private $startRightCommand;

    /**
     * @var CommandInterface
     */
    private $endLeftCommand;

    /**
     * @var CommandInterface
     */
    private $endRightCommand;

    /**
     * @var int - Победившая команда: 1 - левая команда, 2 - правая команда
     */
    private $winner;

    /**
     * @var FullLog
     */
    private $fullLog;

    /**
     * @var Chat
     */
    private $chat;

    /**
     * @var Statistic
     */
    private $statistic;

    /**
     * @var Translation
     */
    private $translation;

    /**
     * @var ScenarioInterface
     */
    private $scenario;

    /**
     * @param CommandInterface $startLeftCommand
     * @param CommandInterface $startRightCommand
     * @param CommandInterface $endLeftCommand
     * @param CommandInterface $endRightCommand
     * @param int $winner
     * @param FullLog $fullLog
     * @param Chat $chat
     * @param ScenarioInterface $scenario
     * @param Statistic $statistic
     * @param Translation $translation
     * @throws ResultException
     */
    public function __construct(
        CommandInterface $startLeftCommand,
        CommandInterface $startRightCommand,
        CommandInterface $endLeftCommand,
        CommandInterface $endRightCommand,
        int $winner,
        FullLog $fullLog,
        Chat $chat,
        ScenarioInterface $scenario,
        Statistic $statistic,
        Translation $translation
    )
    {
        if ($winner !== 1 && $winner !== 2) {
            throw new ResultException(ResultException::INCORRECT_WINNER);
        }

        $this->winner = $winner;
        $this->startLeftCommand = $startLeftCommand;
        $this->startRightCommand = $startRightCommand;
        $this->endLeftCommand = $endLeftCommand;
        $this->endRightCommand = $endRightCommand;
        $this->fullLog = $fullLog;
        $this->chat = $chat;
        $this->statistic = $statistic;
        $this->translation = $translation;
        $this->scenario = $scenario;
    }

    public function getStartLeftCommand(): CommandInterface
    {
        return $this->startLeftCommand;
    }

    public function getStartRightCommand(): CommandInterface
    {
        return $this->startRightCommand;
    }

    public function getEndLeftCommand(): CommandInterface
    {
        return $this->endLeftCommand;
    }

    public function getEndRightCommand(): CommandInterface
    {
        return $this->endRightCommand;
    }

    public function getWinner(): int
    {
        return $this->winner;
    }

    public function getWinnerText(): string
    {
        return $this->winner === 1 ? self::LEFT_COMMAND_WIN : self::RIGHT_COMMAND_WIN;
    }

    public function getFullLog(): FullLog
    {
        return $this->fullLog;
    }

    public function getChat(): Chat
    {
        return $this->chat;
    }

    public function getStatistic(): Statistic
    {
        return $this->statistic;
    }

    public function getTranslation(): TranslationInterface
    {
        return $this->translation;
    }

    public function getScenario(): ScenarioInterface
    {
        return $this->scenario;
    }
}
