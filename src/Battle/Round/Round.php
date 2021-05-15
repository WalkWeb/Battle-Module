<?php

declare(strict_types=1);

namespace Battle\Round;

use Battle\Command\CommandInterface;
use Battle\Statistic\Statistic;
use Battle\Result\Chat\FullLog;
use Battle\Stroke\StrokeFactory;
use Battle\Stroke\StrokeInterface;

class Round implements RoundInterface
{
    private const END          = 'Battle end';
    private const START_ROUND  = 'Start new round';
    private const END_ROUND    = 'All command actions. New round';
    private const START_STROKE = 'Start Stroke';
    private const END_STROKE   = 'End Stroke';
    private const HR           = '<hr>';

    /** @var CommandInterface */
    private $leftCommand;

    /** @var CommandInterface */
    private $rightCommand;

    /** @var int - Команда, которая совершает ход: 1 - leftCommand, 2 - rightCommand */
    private $actionCommand;

    /** @var int */
    private $maxStroke = 20;

    /** @var Statistic */
    private $statistics;

    /** @var FullLog */
    private $chat;

    /** @var bool */
    private $debug;

    /** @var StrokeFactory */
    private $strokeFactory;

    /**
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param int $actionCommand
     * @param Statistic $statistics
     * @param FullLog $chat
     * @param bool|null $debug
     * @param StrokeFactory|null $strokeFactory
     * @throws RoundException
     */
    public function __construct(
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        int $actionCommand,
        Statistic $statistics,
        FullLog $chat,
        ?bool $debug = false,
        ?StrokeFactory $strokeFactory = null
    )
    {
        $this->validateActionCommand($actionCommand);
        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
        $this->actionCommand = $actionCommand;
        $this->statistics = $statistics;
        $this->chat = $chat;
        $this->debug = $debug;
        $this->strokeFactory = $strokeFactory ?? new StrokeFactory();
    }

    /**
     * Выполняет раунд
     *
     * Раундом считается выполненным, когда все живые юниты сделали свой ход. После этого, если обе команды остались
     * живы - сбрасываются параметры $action у юнитов и начинается новый раунд.
     *
     * @return int
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
                $stroke = $this->strokeFactory->create(
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

    /**
     * Возвращает статистику дополненную информацией по текущему раунду
     *
     * @return Statistic
     */
    public function getStatistics(): Statistic
    {
        return $this->statistics;
    }

    /**
     * Выполняет один ход:
     *
     * 1. Добавляет сообщение в чат о новом ходе
     * 2. Выполняет ход
     * 3. Добавляет сообщение в чат о завершении хода
     * 4. Добавляет разделительную линию (todo на удаление, объекты не должны выводить html)
     *
     * @param StrokeInterface $stroke
     */
    private function executeStroke(StrokeInterface $stroke): void
    {
        $this->chat->add('<p>' . self::START_STROKE . ' #' . $this->statistics->getStrokeNumber() . '</p>');
        $stroke->handle();
        $this->chat->add('<p>' . self::END_STROKE . ' #' . $this->statistics->getStrokeNumber() . '</p>');
        $this->chat->add('<p>' . self::HR . '</p>');
    }

    /**
     * Стартует раунд:
     *
     * 1. В чат добавляет сообщение о том, что раунд стартовал
     */
    private function startRound(): void
    {
        $this->chat->add('<p>' . self::START_ROUND . ' #' . $this->statistics->getRoundNumber() . '</p>');
    }

    /**
     * Завершает раунд:
     *
     * 1. В чат добавляет сообщение о том, что раунд завершен
     * 2. Обоим командам сообщается, что раунд завершен
     * 3. Возвращается номер команды, которая будет делать следующий ход
     *
     * @return int
     */
    private function endRound(): int
    {
        $this->chat->add('<p>' . self::END_ROUND . '</p>');
        $this->leftCommand->newRound();
        $this->rightCommand->newRound();
        return $this->actionCommand;
    }

    /**
     * Завершает бой:
     *
     * 1. В чат добавляет сообщение о том, что бой завершен
     * 2. handle() должен вернуть номер команды, которая ходит следующей, но так как бой закончился - нам не важно,
     *    что отдавать, и отдаем просто 0
     *
     * @return int
     */
    private function endBattle(): int
    {
        $this->chat->add('<p>' . self::END . '</p>');
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
}
