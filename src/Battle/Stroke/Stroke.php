<?php

declare(strict_types=1);

namespace Battle\Stroke;

use Battle\Command\CommandInterface;
use Battle\Statistic\BattleStatistic;
use Battle\Chat\Chat;
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

    /** @var BattleStatistic */
    private $statistics;

    /** @var Chat */
    private $chat;

    /** @var bool */
    private $debug;

    /** @var ViewFactory */
    private $viewFactory;

    /**
     * @param int $actionCommand
     * @param UnitInterface $actionUnit
     * @param CommandInterface $leftCommand
     * @param CommandInterface $rightCommand
     * @param BattleStatistic $statistics
     * @param Chat $chat
     * @param bool|null $debug
     * @param ViewFactory|null $viewFactory
     */
    public function __construct(
        int $actionCommand,
        UnitInterface $actionUnit,
        CommandInterface $leftCommand,
        CommandInterface $rightCommand,
        BattleStatistic $statistics,
        Chat $chat,
        ?bool $debug = false,
        ?ViewFactory $viewFactory = null
    )
    {
        $this->actionCommand = $actionCommand;
        $this->actionUnit = $actionUnit;
        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
        $this->statistics = $statistics;
        $this->chat = $chat;
        $this->debug = $debug;
        $this->viewFactory = $viewFactory ?? new ViewFactory();
    }

    /**
     * Совершает ход одного юнита в бою
     */
    public function handle(): void
    {
        if ($this->debug) {
            $view = $this->viewFactory->create();
            $this->chat->add($view->render($this->leftCommand, $this->rightCommand));
        }

        //--------------------------------------------------------------------------------------------------------------

        $enemyCommand = $this->actionCommand === 1 ? $this->rightCommand : $this->leftCommand;
        $alliesCommand = $this->actionCommand === 1 ? $this->leftCommand : $this->rightCommand;

        $actionCollection = $this->actionUnit->getAction($enemyCommand, $alliesCommand);

        foreach ($actionCollection->getActions() as $action) {

            if (!$enemyCommand->isAlive()) {
                break;
            }

            $message = $action->handle();
            $this->statistics->addUnitAction($action);
            $this->chat->add($message);
        }

        //--------------------------------------------------------------------------------------------------------------

        $this->actionUnit->madeAction();

        if ($this->debug) {
            $view = $this->viewFactory->create();
            $this->chat->add($view->render($this->leftCommand, $this->rightCommand));
        }
    }
}