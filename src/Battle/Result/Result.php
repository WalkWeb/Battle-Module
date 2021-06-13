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
    private $leftCommand;

    /**
     * @var CommandInterface
     */
    private $rightCommand;

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
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param int $winner
     * @param FullLog $fullLog
     * @param Chat $chat
     * @param Statistic $statistic
     * @param Translation $translation
     * @param ScenarioInterface $scenario
     * @throws ResultException
     */
    public function __construct(
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
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
        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
        $this->fullLog = $fullLog;
        $this->chat = $chat;
        $this->statistic = $statistic;
        $this->translation = $translation;
        $this->scenario = $scenario;
    }

    public function getLeftCommand(): CommandInterface
    {
        return $this->leftCommand;
    }

    public function getRightCommand(): CommandInterface
    {
        return $this->rightCommand;
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
