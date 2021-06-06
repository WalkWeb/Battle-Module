<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Action\ActionCollection;
use Battle\Action\ActionInterface;
use Battle\Action\Damage\DamageAction;
use Battle\Action\Heal\HealAction;
use Battle\Action\Summon\SummonAction;
use Battle\Command\CommandInterface;
use Battle\Translation\TranslationException;
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
        if ($this->class && $this->concentration >= self::MAX_CONS) {
            $this->concentration = 0;
            return $this->class->getAbility($this, $enemyCommand, $alliesCommand);
        }

        // Пока концентрация применяется сразу, при попытке сделать атаку, можно переделать так, чтобы добавлялась
        // только при попадании по цели
        $this->addConcentration(self::ADD_CON_ACTION_UNIT);

        return $this->getDamageAction($enemyCommand, $alliesCommand);
    }

    /**
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionInterface
     */
    public function  getBaseAttack(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionInterface
    {
        return new DamageAction($this, $enemyCommand, $alliesCommand, $this->message);
    }

    /**
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws Exception
     */
    private function getDamageAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        $collection = new ActionCollection();
        $attacks = $this->calculateAttackSpeed();

        for ($i = 0; $i < $attacks; $i++) {
            $collection->add(new DamageAction($this, $enemyCommand, $alliesCommand, $this->message));
        }

        return $collection;
    }

    // ---------------------------------------------- HANDLE ACTION ----------------------------------------------------

    /**
     * Принимает и обрабатывает абстрактное действие от другого юнита.
     *
     * @uses applyDamageAction, applyHealAction, applySummonAction
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

        $this->addConcentration(self::ADD_CON_RECEIVING_UNIT);

        return $this->$method($action);
    }

    /**
     * @param DamageAction $action
     * @return string
     * @throws TranslationException
     */
    private function applyDamageAction(DamageAction $action): string
    {
        $primordialLife = $this->life;

        $this->life -= $action->getPower();
        if ($this->life < 0) {
            $this->life = 0;
        }

        $action->setFactualPower($primordialLife - $this->life);

        return $this->message->damage($action);
    }

    /**
     * @param HealAction $action
     * @return string
     * @throws TranslationException
     */
    private function applyHealAction(HealAction $action): string
    {
        $primordialLife = $this->life;

        $this->life += $action->getPower();
        if ($this->life > $this->totalLife) {
            $this->life = $this->totalLife;
        }

        $action->setFactualPower($this->life - $primordialLife);

        return $this->message->heal($action);
    }

    /**
     * Добавление юнита в команду происходит в самом SummonAction, от пользователя нужно только сообщение о действии
     *
     * В тоже время, в будущем могут быть добавлены параметры, влияющие на силу саммонов
     *
     * @param SummonAction $action
     * @return string
     * @throws TranslationException
     */
    private function applySummonAction(SummonAction $action): string
    {
        return $this->message->summon($action);
    }
}
