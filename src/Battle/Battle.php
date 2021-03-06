<?php

declare(strict_types=1);

namespace Battle;

use Battle\Container\ContainerException;
use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
use Battle\Translation\Translation;
use Battle\Result\Result;
use Battle\Result\ResultInterface;
use Battle\Translation\TranslationInterface;
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

    /** @var ContainerInterface */
    private $container;

    /**
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param ContainerInterface $container
     * @param bool|null $debug
     * @throws Exception
     */
    public function __construct(
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        ContainerInterface $container,
        ?bool $debug = true
    )
    {
        $this->checkDoubleUnitId($leftCommand, $rightCommand);
        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
        $this->actionCommand = random_int(1, 2);
        $this->container = $container;
        $this->debug = $debug;
    }

    /**
     * Обрабатывает бой, возвращая результат выполнения
     *
     * @return ResultInterface
     * @throws Exception
     */
    public function handle(): ResultInterface
    {
        $i = 0;

        $startLeftCommand = clone $this->leftCommand;
        $startRightCommand = clone $this->rightCommand;

        $roundFactory = $this->container->getRoundFactory();
        $statistics = $this->container->getStatistic();

        while ($i < $this->maxRound) {
            $round = $roundFactory->create(
                $this->leftCommand,
                $this->rightCommand,
                $this->actionCommand,
                $this->container,
                $this->debug,
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
                    $this->container
                );
            }

            $statistics->increasedRound();
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
     * TODO На удаление
     *
     * @return Translation
     * @throws ContainerException
     */
    public function getTranslation(): TranslationInterface
    {
        return $this->container->getTranslation();
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
