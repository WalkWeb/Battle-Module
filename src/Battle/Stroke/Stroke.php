<?php

declare(strict_types=1);

namespace Battle\Stroke;

use Battle\Command\CommandInterface;
use Battle\Result\Chat\Chat;
use Battle\Result\Scenario\ScenarioInterface;
use Battle\Statistic\Statistic;
use Battle\Result\FullLog\FullLog;
use Battle\Statistic\StatisticException;
use Battle\Statistic\StatisticInterface;
use Battle\Unit\UnitInterface;
use Battle\View\ViewFactory;

class Stroke implements StrokeInterface
{
    /**
     * Команда, которая совершает ход: 1 - leftCommand, 2 - rightCommand
     *
     * @var int
     */
    private $actionCommand;

    /**
     * Юнит совершающий действие
     *
     * @var UnitInterface
     */
    private $actionUnit;

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
     * Чат боя
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
     * @var ViewFactory
     */
    private $viewFactory;

    /**
     * @param int $actionCommand
     * @param UnitInterface $actionUnit
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param StatisticInterface $statistics
     * @param FullLog $fullLog
     * @param Chat $chat
     * @param ScenarioInterface $scenario
     * @param bool|null $debug
     * @param ViewFactory|null $viewFactory
     */
    public function __construct(
        int $actionCommand,
        UnitInterface $actionUnit,
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        StatisticInterface $statistics,
        FullLog $fullLog,
        Chat $chat,
        ScenarioInterface $scenario,
        ?bool $debug = false,
        ?ViewFactory $viewFactory = null
    )
    {
        $this->actionCommand = $actionCommand;
        $this->actionUnit = $actionUnit;
        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
        $this->statistics = $statistics;
        $this->fullLog = $fullLog;
        $this->chat = $chat;
        $this->scenario = $scenario;
        $this->debug = $debug;
        $this->viewFactory = $viewFactory ?? new ViewFactory();
    }

    /**
     * Совершает ход одного юнита в бою
     *
     * @throws StatisticException
     */
    public function handle(): void
    {
        //--------------------------------------------------------------------------------------------------------------

        $enemyCommand = $this->actionCommand === 1 ? $this->rightCommand : $this->leftCommand;
        $alliesCommand = $this->actionCommand === 1 ? $this->leftCommand : $this->rightCommand;

        $actionCollection = $this->actionUnit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection as $action) {

            if (!$enemyCommand->isAlive()) {
                break;
            }

            $message = $action->handle();

            if (!$action->isSuccessHandle()) {
                $action = $this->actionUnit->getBaseAttack($enemyCommand, $alliesCommand);
                $message = $action->handle();
            }

            $this->statistics->addUnitAction($action);
            $this->scenario->addAction($action, $this->statistics);
            $this->fullLog->add('<p>' . $message . '</p>');
            $this->chat->add($message);
        }

        //--------------------------------------------------------------------------------------------------------------

        $this->actionUnit->madeAction();

        $view = $this->viewFactory->create();
        $this->fullLog->add($view->renderCommandView($this->leftCommand, $this->rightCommand));
    }
}
