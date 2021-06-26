<?php

declare(strict_types=1);

namespace Battle\Round;

use Battle\Command\CommandInterface;
use Battle\Result\Chat\Chat;
use Battle\Result\Scenario\ScenarioInterface;
use Battle\Statistic\Statistic;
use Battle\Result\FullLog\FullLog;
use Battle\Stroke\StrokeFactory;
use Battle\Stroke\StrokeInterface;
use Battle\Translation\Translation;
use Battle\Translation\TranslationException;
use Exception;

class Round implements RoundInterface
{
    private const END          = 'Battle end';
    private const START_ROUND  = 'Start new round';
    private const END_ROUND    = 'All command actions. New round';
    private const START_STROKE = 'Start Stroke';
    private const END_STROKE   = 'End Stroke';
    private const HR           = '<hr>';

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
     * Статистика по юнитам в бою
     *
     * @var Statistic
     */
    private $statistics;

    /**
     * Полный лог боя
     *
     * @var FullLog
     */
    private $fullLog;

    /**
     * Чат
     *
     * @var Chat
     */
    private $chat;

    /**
     * @var ScenarioInterface
     */
    private $scenario;

    /**
     * TODO На удаление? Или на расширение механики вывода результата?
     *
     * @var bool
     */
    private $debug;

    /**
     * @var StrokeFactory
     */
    private $strokeFactory;

    /**
     * @var Translation
     */
    private $translation;

    /**
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param int $actionCommand
     * @param Statistic $statistics
     * @param FullLog $fullLog
     * @param Chat $chat
     * @param ScenarioInterface $scenario
     * @param bool|null $debug
     * @param StrokeFactory|null $strokeFactory
     * @param Translation|null $translation
     * @throws RoundException
     */
    public function __construct(
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        int $actionCommand,
        Statistic $statistics,
        FullLog $fullLog,
        Chat $chat,
        ScenarioInterface $scenario,
        ?bool $debug = false,
        ?StrokeFactory $strokeFactory = null,
        ?Translation $translation = null
    )
    {
        $this->validateActionCommand($actionCommand);
        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
        $this->actionCommand = $actionCommand;
        $this->statistics = $statistics;
        $this->fullLog = $fullLog;
        $this->chat = $chat;
        $this->scenario = $scenario;
        $this->debug = $debug;
        $this->strokeFactory = $strokeFactory ?? new StrokeFactory();
        $this->translation = $translation ?? new Translation();
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
                $stroke = $this->strokeFactory->create(
                    $this->actionCommand,
                    $actionUnit,
                    $this->leftCommand,
                    $this->rightCommand,
                    $this->statistics,
                    $this->fullLog,
                    $this->chat,
                    $this->scenario,
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
     */
    public function getStatistics(): Statistic
    {
        return $this->statistics;
    }

    /**
     * Выполняет один ход:
     *
     * 1. Добавляет информацию в лог о новом ходе
     * 2. Выполняет ход
     * 3. Добавляет информацию в лог о завершении хода
     * 4. Добавляет разделительную линию (todo на удаление, объекты не должны выводить html)
     *
     * @param StrokeInterface $stroke
     * @throws Exception
     */
    private function executeStroke(StrokeInterface $stroke): void
    {
        $this->fullLog->add(
            '<p>' . $this->translation->trans(self::START_STROKE) . ' #' . $this->statistics->getStrokeNumber() . '</p>'
        );

        $stroke->handle();

        $this->fullLog->add(
            '<p>' .  $this->translation->trans(self::END_STROKE) . ' #' . $this->statistics->getStrokeNumber() . '</p>'
        );

        $this->fullLog->add(self::HR);
    }

    /**
     * Стартует раунд:
     *
     * 1. В лог добавляет информацию о том, что раунд стартовал
     *
     * @throws TranslationException
     */
    private function startRound(): void
    {
        $this->fullLog->add(
            '<p>' . $this->translation->trans(self::START_ROUND) . ' #' . $this->statistics->getRoundNumber() . '</p>'
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
     * @throws TranslationException
     */
    private function endRound(): int
    {
        $this->fullLog->add('<p>' . $this->translation->trans(self::END_ROUND) . '</p>');
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
     */
    private function endBattle(): int
    {
        $this->fullLog->add('<p>' . self::END . '</p>');
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
