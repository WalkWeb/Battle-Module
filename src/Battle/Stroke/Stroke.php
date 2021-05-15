<?php

declare(strict_types=1);

namespace Battle\Stroke;

use Battle\Command\CommandInterface;
use Battle\Statistic\Statistic;
use Battle\Result\FullLog\FullLog;
use Battle\Statistic\StatisticException;
use Battle\Unit\UnitInterface;
use Battle\View\ViewFactory;

class Stroke implements StrokeInterface
{
    /** @var int - Команда, которая совершает ход: 1 - leftCommand, 2 - rightCommand */
    private $actionCommand;

    /** @var UnitInterface - Юнит совершающий действие (атаку) */
    private $actionUnit;

    /** @var CommandInterface */
    private $leftCommand;

    /** @var CommandInterface */
    private $rightCommand;

    /** @var Statistic */
    private $statistics;

    /** @var FullLog */
    private $fullLog;

    /** @var bool */
    private $debug;

    /** @var ViewFactory */
    private $viewFactory;

    /**
     * @param int $actionCommand
     * @param UnitInterface $actionUnit
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param Statistic $statistics
     * @param FullLog $fullLog
     * @param bool|null $debug
     * @param ViewFactory|null $viewFactory
     */
    public function __construct(
        int $actionCommand,
        UnitInterface $actionUnit,
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        Statistic $statistics,
        FullLog $fullLog,
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
        $this->debug = $debug;
        $this->viewFactory = $viewFactory ?? new ViewFactory();
    }

    /**
     * Совершает ход одного юнита в бою
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
            $this->statistics->addUnitAction($action);
            $this->fullLog->add('<p>' . $message . '</p>');
        }

        //--------------------------------------------------------------------------------------------------------------

        $this->actionUnit->madeAction();

        $view = $this->viewFactory->create();
        $this->fullLog->add($view->renderCommandView($this->leftCommand, $this->rightCommand));
    }
}
