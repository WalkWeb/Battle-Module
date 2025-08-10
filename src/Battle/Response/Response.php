<?php

declare(strict_types=1);

namespace Battle\Response;

use Battle\Container\ContainerException;
use Battle\Container\ContainerInterface;
use Battle\Response\Chat\ChatInterface;
use Battle\Command\CommandInterface;
use Battle\Response\FullLog\FullLogInterface;
use Battle\Response\Scenario\ScenarioInterface;
use Battle\Response\Statistic\StatisticInterface;
use Battle\Translation\TranslationInterface;

class Response implements ResponseInterface
{
    private CommandInterface $startLeftCommand;

    private CommandInterface $startRightCommand;

    private CommandInterface $endLeftCommand;

    private CommandInterface $endRightCommand;

    /**
     * Победившая команда: 1 - левая команда, 2 - правая команда
     */
    private int $winner;

    private ContainerInterface $container;

    /**
     * @param CommandInterface $startLeftCommand
     * @param CommandInterface $startRightCommand
     * @param CommandInterface $endLeftCommand
     * @param CommandInterface $endRightCommand
     * @param int $winner
     * @param ContainerInterface $container
     * @throws ResponseException
     */
    public function __construct(
        CommandInterface $startLeftCommand,
        CommandInterface $startRightCommand,
        CommandInterface $endLeftCommand,
        CommandInterface $endRightCommand,
        int $winner,
        ContainerInterface $container
    ) {
        if ($winner !== 1 && $winner !== 2) {
            throw new ResponseException(ResponseException::INCORRECT_WINNER);
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
     * @throws ContainerException
     */
    public function getFullLog(): FullLogInterface
    {
        return $this->container->getFullLog();
    }

    /**
     * @throws ContainerException
     */
    public function getChat(): ChatInterface
    {
        return $this->container->getChat();
    }

    /**
     * @throws ContainerException
     */
    public function getStatistic(): StatisticInterface
    {
        return $this->container->getStatistic();
    }

    /**
     * @throws ContainerException
     */
    public function getTranslation(): TranslationInterface
    {
        return $this->container->getTranslation();
    }

    /**
     * @throws ContainerException
     */
    public function getScenario(): ScenarioInterface
    {
        return $this->container->getScenario();
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
