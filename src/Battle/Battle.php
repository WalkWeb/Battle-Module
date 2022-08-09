<?php

declare(strict_types=1);

namespace Battle;

use Battle\Command\CommandInterface;
use Battle\Container\ContainerInterface;
use Battle\Result\Result;
use Battle\Result\ResultInterface;
use Exception;

class Battle implements BattleInterface
{
    public const LIMIT_ROUND_MESSAGE = 'Limit round. Winner by max life';

    /**
     * @var CommandInterface
     */
    private CommandInterface $leftCommand;

    /**
     * @var CommandInterface
     */
    private CommandInterface $rightCommand;

    /**
     * Команда, которая совершает ход: 1 - leftCommand, 2 - rightCommand
     *
     * @var int
     */
    private int $actionCommand;

    /**
     * TODO Лучше переделать на лимит Stroke
     *
     * @var int
     */
    private int $maxRound = 100;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param ContainerInterface $container
     * @param int|null $actionCommand
     * @throws Exception
     */
    public function __construct(
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        ContainerInterface $container,
        ?int $actionCommand = null
    )
    {
        $this->checkDoubleUnitId($leftCommand, $rightCommand);
        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
        $this->actionCommand = $this->createActionCommand($actionCommand);
        $this->container = $container;
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
                $this->container
            );

            // Выполняем раунд, получая номер команды, которая будет ходить следующей
            $this->actionCommand = $round->handle();

            // Проверяем живых в командах
            if (!$this->leftCommand->isAlive() || !$this->rightCommand->isAlive()) {
                break;
            }

            $statistics->increasedRound();
            $i++;
        }

        if ($i === $this->maxRound) {
            $this->container->getFullLog()->addText(
                $this->container->getTranslation()->trans(self::LIMIT_ROUND_MESSAGE)
            );
        }

        return $this->getResult($startLeftCommand, $startRightCommand, $this->getWinner());
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
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

    /**
     * Создает и возвращает объект результата боя
     *
     * @param CommandInterface $startLeftCommand
     * @param CommandInterface $startRightCommand
     * @param int $winner
     * @return ResultInterface
     * @throws Exception
     */
    private function getResult(CommandInterface $startLeftCommand, CommandInterface $startRightCommand, int $winner): ResultInterface
    {
        return new Result(
            $startLeftCommand,
            $startRightCommand,
            $this->leftCommand,
            $this->rightCommand,
            $winner,
            $this->container
        );
    }

    /**
     * Определяет победителя
     *
     * Если одна из команд погибла - выбирается выжившая, если же обе команды живы (бой закончился по лимиту раундов) то
     * выбирается та команда, у которой осталось больше всего здоровья
     *
     * @return int
     */
    private function getWinner(): int
    {
        if (!$this->leftCommand->isAlive() || !$this->rightCommand->isAlive()) {
            return $this->leftCommand->isAlive() ? 1 : 2;
        }

        return $this->leftCommand->getTotalLife() > $this->rightCommand->getTotalLife() ? 1 : 2;
    }

    /**
     * @param int|null $actionCommand
     * @return int
     * @throws Exception
     */
    private function createActionCommand(?int $actionCommand): int
    {
        if ($actionCommand === null) {
            return random_int(1, 2);
        }

        if ($actionCommand !== 1 && $actionCommand !== 2) {
            throw new BattleException(BattleException::INCORRECT_START_COMMAND);
        }

        return $actionCommand;
    }
}
