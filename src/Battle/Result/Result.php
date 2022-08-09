<?php

declare(strict_types=1);

namespace Battle\Result;

use Battle\Container\ContainerException;
use Battle\Container\ContainerInterface;
use Battle\Result\Chat\ChatInterface;
use Battle\Command\CommandInterface;
use Battle\Result\FullLog\FullLogInterface;
use Battle\Result\Scenario\ScenarioInterface;
use Battle\Result\Statistic\StatisticInterface;
use Battle\Translation\TranslationInterface;

class Result implements ResultInterface
{
    /**
     * @var CommandInterface
     */
    private CommandInterface $startLeftCommand;

    /**
     * @var CommandInterface
     */
    private CommandInterface $startRightCommand;

    /**
     * @var CommandInterface
     */
    private CommandInterface $endLeftCommand;

    /**
     * @var CommandInterface
     */
    private CommandInterface $endRightCommand;

    /**
     * @var int - Победившая команда: 1 - левая команда, 2 - правая команда
     */
    private int $winner;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @param CommandInterface $startLeftCommand
     * @param CommandInterface $startRightCommand
     * @param CommandInterface $endLeftCommand
     * @param CommandInterface $endRightCommand
     * @param int $winner
     * @param ContainerInterface $container
     * @throws ResultException
     */
    public function __construct(
        CommandInterface $startLeftCommand,
        CommandInterface $startRightCommand,
        CommandInterface $endLeftCommand,
        CommandInterface $endRightCommand,
        int $winner,
        ContainerInterface $container
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
        $this->container = $container;
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

    /**
     * @return FullLogInterface
     * @throws ContainerException
     */
    public function getFullLog(): FullLogInterface
    {
        return $this->container->getFullLog();
    }

    /**
     * @return ChatInterface
     * @throws ContainerException
     */
    public function getChat(): ChatInterface
    {
        return $this->container->getChat();
    }

    /**
     * @return StatisticInterface
     * @throws ContainerException
     */
    public function getStatistic(): StatisticInterface
    {
        return $this->container->getStatistic();
    }

    /**
     * @return TranslationInterface
     * @throws ContainerException
     */
    public function getTranslation(): TranslationInterface
    {
        return $this->container->getTranslation();
    }

    /**
     * @return ScenarioInterface
     * @throws ContainerException
     */
    public function getScenario(): ScenarioInterface
    {
        return $this->container->getScenario();
    }
}
