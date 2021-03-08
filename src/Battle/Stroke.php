<?php

declare(strict_types=1);

namespace Battle;

use Battle\Statistic\BattleStatistic;
use Battle\Chat\Chat;
use Battle\Unit\Unit;

class Stroke
{
    /** @var int - Команда, которая совершает ход: 1 - leftCommand, 2 - rightCommand */
    private $actionCommand;

    /** @var Unit - Юнит совершающий действие (атаку) */
    private $actionUnit;

    /** @var Command */
    private $leftCommand;

    /** @var Command */
    private $rightCommand;

    /** @var BattleStatistic */
    private $statistics;

    /** @var Chat */
    private $chat;

    /** @var bool */
    private $debug;

    /**
     * @param int $actionCommand
     * @param Unit $actionUnit
     * @param Command $leftCommand
     * @param Command $rightCommand
     * @param BattleStatistic $statistics
     * @param Chat $chat
     * @param bool $debug
     */
    public function __construct(
        int $actionCommand,
        Unit $actionUnit,
        Command $leftCommand,
        Command $rightCommand,
        BattleStatistic $statistics,
        Chat $chat,
        bool $debug = false
    )
    {
        $this->actionCommand = $actionCommand;
        $this->actionUnit = $actionUnit;
        $this->leftCommand = $leftCommand;
        $this->rightCommand = $rightCommand;
        $this->statistics = $statistics;
        $this->chat = $chat;
        $this->debug = $debug;
    }

    /**
     * @throws Exception\ActionCollectionException
     */
    public function handle(): void
    {
        if ($this->debug) {
            $view = new View($this->leftCommand, $this->rightCommand);
            $this->chat->add($view());
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
            $view = new View($this->leftCommand, $this->rightCommand);
            $this->chat->add($view());
        }
    }
}
