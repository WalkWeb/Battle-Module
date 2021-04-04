<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Action\ActionCollection;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\HealAction;
use Battle\Chat\Message;
use Battle\Command\CommandInterface;
use Exception;

class Unit extends AbstractUnit
{
    /**
     * Возвращает абстрактное действие (действия) от юнита в его ходе.
     *
     * В нашей логике юнит сам решает, какое действие ему совершать - это может быть как обычная атака, так и какая-то
     * способность, зависящая от класса.
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws Exception
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        if ($this->concentration >= self::MAX_CONS) {
            $this->concentration = 0;
            return $this->class->getAbility($this, $enemyCommand, $alliesCommand);
        }

        return $this->getDamageAction($enemyCommand, $alliesCommand);
    }

    /**
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws Exception
     */
    private function getDamageAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        $attacks = $this->calculateAttackSpeed();
        $array = [];

        for ($i = 0; $i < $attacks; $i++) {
            $array[] = new DamageAction($this, $enemyCommand, $alliesCommand);
        }

        return new ActionCollection($array);
    }

    // ---------------------------------------------- HANDLE ACTION ----------------------------------------------------

    /**
     * Принимает и обрабатывает абстрактное действие от другого юнита.
     *
     * @uses applyDamageAction, applyHealAction
     * @param ActionInterface $action
     * @return string - Сообщение о произошедшем действии
     * @throws Exception
     */
    public function applyAction(ActionInterface $action): string
    {
        $method = $action->getHandleMethod();

        if (!method_exists($this, $method)) {
            throw new UnitException(UnitException::UNDEFINED_ACTION_METHOD);
        }

        return $this->$method($action);
    }

    /**
     * @param DamageAction $action
     * @return string
     */
    private function applyDamageAction(DamageAction $action): string
    {
        $primordialLife = $this->life;

        $this->life -= $action->getPower();
        if ($this->life < 0) {
            $this->life = 0;
        }

        $action->setFactualPower($primordialLife - $this->life);

        return Message::damage($action);
    }

    /**
     * @param HealAction $action
     * @return string
     */
    private function applyHealAction(HealAction $action): string
    {
        $primordialLife = $this->life;

        $this->life += $action->getPower();
        if ($this->life > $this->totalLife) {
            $this->life = $this->totalLife;
        }

        $action->setFactualPower($this->life - $primordialLife);

        return Message::heal($action);
    }
}
