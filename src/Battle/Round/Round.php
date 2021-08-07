<?php

declare(strict_types=1);

namespace Battle\Round;

use Battle\Command\CommandInterface;
use Battle\Container\Container;
use Battle\Container\ContainerException;
use Battle\Container\ContainerInterface;
use Battle\Result\Statistic\Statistic;
use Battle\Result\Statistic\StatisticInterface;
use Battle\Stroke\StrokeInterface;
use Exception;

class Round implements RoundInterface
{
    private const END          = 'Battle end';
    private const START_ROUND  = 'Start new round';
    private const END_ROUND    = 'All command actions. New round';
    private const START_STROKE = 'Start Stroke';
    private const END_STROKE   = 'End Stroke';

    /**
     * Левая команда
     *
     * @var CommandInterface
     */
    private $leftCommand;

    /**
     * Правая команда
     *
     * @var CommandInterface
     */
    private $rightCommand;

    /**
     * Команда, которая совершает ход: 1 - leftCommand, 2 - rightCommand
     *
     * @var int
     */
    private $actionCommand;

    /**
     * Максимальное количество ходов в рамках одного раунда
     *
     * @var int
     */
    private $maxStroke = 20;

    /**
     * @var Container
     */
    private $container;

    /**
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param int $actionCommand
     * @param ContainerInterface $container
     * @throws RoundException
     */
    public function __construct(
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        int $actionCommand,
        ContainerInterface $container
    )
    {
        $this->validateActionCommand($actionCommand);
        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
        $this->actionCommand = $actionCommand;
        $this->container = $container;
    }

    /**
     * Выполняет раунд
     *
     * Раундом считается выполненным, когда все живые юниты сделали свой ход. После этого, если обе команды остались
     * живы - сбрасываются параметры $action у юнитов и начинается новый раунд.
     *
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        $this->startRound();

        $i = 0;
        while ($i < $this->maxStroke) {

            $actionUnit = $this->actionCommand === 1 ? $this->leftCommand->getUnitForAction() : $this->rightCommand->getUnitForAction();

            if ($actionUnit) {

                // Выполняем один ход - т.е. действие одного юнита
                $stroke = $this->container->getStrokeFactory()->create(
                    $this->actionCommand,
                    $actionUnit,
                    $this->leftCommand,
                    $this->rightCommand,
                    $this->container
                );

                $this->executeStroke($stroke);

                // Проверяем живых в командах
                if (!$this->leftCommand->isAlive() || !$this->rightCommand->isAlive()) {
                    return $this->endBattle();
                }

                $this->container->getStatistic()->increasedStroke();

                // Проверяем, остались ли юниты, которые не ходили
                if (!$this->leftCommand->isAction() && !$this->rightCommand->isAction()) {
                    // Чтобы новый раунд начала следующая команда - также меняем действующую команду
                    $this->changeActionCommand();
                    return $this->endRound();
                }
            }

            // Независимо от того, были ли юнит для хода или нет - меняем следующую действующую команду
            $this->changeActionCommand();
            $i++;
        }

        throw new RoundException(RoundException::UNEXPECTED_ENDING);
    }

    /**
     * Возвращает статистику дополненную информацией по текущему раунду
     *
     * @return Statistic
     * @throws ContainerException
     */
    public function getStatistics(): StatisticInterface
    {
        return $this->container->getStatistic();
    }

    /**
     * Выполняет один ход:
     *
     * 1. Добавляет информацию в лог о новом ходе
     * 2. Выполняет ход
     * 3. Добавляет информацию в лог о завершении хода
     * 4. Добавляет разделительную линию
     *
     * @param StrokeInterface $stroke
     * @throws Exception
     */
    private function executeStroke(StrokeInterface $stroke): void
    {
        $this->container->getFullLog()->addText(
            $this->container->getTranslation()->trans(self::START_STROKE) . ' #' . $this->container->getStatistic()->getStrokeNumber()
        );

        $stroke->handle();

        $this->container->getFullLog()->addText(
            $this->container->getTranslation()->trans(self::END_STROKE) . ' #' . $this->container->getStatistic()->getStrokeNumber()
        );

        $this->container->getFullLog()->addLine();
    }

    /**
     * Стартует раунд:
     *
     * 1. В лог добавляет информацию о том, что раунд стартовал
     *
     * @throws ContainerException
     */
    private function startRound(): void
    {
        $this->container->getFullLog()->addText(
            $this->container->getTranslation()->trans(self::START_ROUND) . ' #' . $this->container->getStatistic()->getRoundNumber()
        );
    }

    /**
     * Завершает раунд:
     *
     * 1. В лог добавляет информацию о том, что раунд завершен
     * 2. Обоим командам сообщается, что раунд завершен
     * 3. Возвращается номер команды, которая будет делать следующий ход
     *
     * @return int
     * @throws ContainerException
     */
    private function endRound(): int
    {
        $this->container->getFullLog()->addText($this->container->getTranslation()->trans(self::END_ROUND));
        $this->leftCommand->newRound();
        $this->rightCommand->newRound();
        return $this->actionCommand;
    }

    /**
     * Завершает бой:
     *
     * 1. В лог добавляет информацию о том, что бой завершен
     * 2. handle() должен вернуть номер команды, которая ходит следующей, но так как бой закончился - нам не важно,
     *    что отдавать, и отдаем просто 0
     *
     * @return int
     * @throws ContainerException
     */
    private function endBattle(): int
    {
        $this->container->getFullLog()->addText(self::END);
        return 0;
    }

    /**
     * Проверяет корректность номера активной команды - может иметь значение только 1 или 2
     *
     * @param int $actionCommand
     * @throws RoundException
     */
    private function validateActionCommand(int $actionCommand): void
    {
        if ($actionCommand !== 1 && $actionCommand !== 2) {
            throw new RoundException(RoundException::INCORRECT_START_COMMAND);
        }
    }

    private function changeActionCommand(): void
    {
        $this->actionCommand = $this->actionCommand === 1 ? $this->actionCommand = 2 : 1;
    }
}
