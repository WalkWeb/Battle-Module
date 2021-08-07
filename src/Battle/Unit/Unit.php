<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\HealAction;
use Battle\Action\WaitAction;
use Battle\Action\SummonAction;
use Battle\Command\CommandInterface;
use Battle\Container\ContainerException;
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
        // Способность применяется если:
        // 1. Есть способность доступная для использования
        // 2. Способность может быть использована (например, есть цель для лечения)
        if (($ability = $this->getAbility()) && $ability->canByUsed($enemyCommand, $alliesCommand)) {
            $ability->usage();
            return $ability->getAction($enemyCommand, $alliesCommand);
        }

        // Пока концентрация применяется сразу, при попытке сделать атаку, можно переделать так, чтобы добавлялась
        // только при попадании по цели
        $this->addConcentration(self::ADD_CON_ACTION_UNIT);
        $this->addRage(self::ADD_RAGE_ACTION_UNIT);

        // Базовую атака не проверяется на canByUsed(), потому что такой ситуации быть не должно - если противников нет
        // Бой должен закончиться. Плюс, это избавляет от дополнительных проверок на каждую атаку
        return $this->getDamageAction($enemyCommand, $alliesCommand);
    }

    /**
     * Принимает и обрабатывает абстрактное действие от другого юнита.
     *
     * @uses applyDamageAction, applyHealAction, applySummonAction, applyWaitAction
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
        $this->addRage(self::ADD_RAGE_RECEIVING_UNIT);

        return $this->$method($action);
    }

    /**
     * Обрабатывает action на получение урона
     *
     * @param DamageAction $action
     * @return string
     * @throws ActionException
     * @throws ContainerException
     */
    private function applyDamageAction(DamageAction $action): string
    {
        $primordialLife = $this->life;

        $this->life -= $action->getPower();
        if ($this->life < 0) {
            $this->life = 0;
        }

        $action->setFactualPower($primordialLife - $this->life);

        return $this->container->getMessage()->damage($action);
    }

    /**
     * Обрабатывает action на лечение
     *
     * @param HealAction $action
     * @return string
     * @throws ActionException
     * @throws ContainerException
     */
    private function applyHealAction(HealAction $action): string
    {
        $primordialLife = $this->life;

        $this->life += $action->getPower();
        if ($this->life > $this->totalLife) {
            $this->life = $this->totalLife;
        }

        $action->setFactualPower($this->life - $primordialLife);

        return $this->container->getMessage()->heal($action);
    }

    /**
     * Обрабатывает action на призыв нового юнита
     *
     * Добавление юнита в команду происходит в самом SummonAction, от пользователя нужно только сообщение о действии
     *
     * В тоже время, в будущем могут быть добавлены параметры, влияющие на силу саммонов
     *
     * @param SummonAction $action
     * @return string
     * @throws ContainerException
     */
    private function applySummonAction(SummonAction $action): string
    {
        return $this->container->getMessage()->summon($action);
    }

    /**
     * Обрабатывает action на пропуск хода
     *
     * @param WaitAction $action
     * @return string
     * @throws ContainerException
     */
    private function applyWaitAction(WaitAction $action): string
    {
        return $this->container->getMessage()->wait($action);
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
            $collection->add(new DamageAction($this, $enemyCommand, $alliesCommand, $this->container->getMessage(), $this->damage));
        }

        if (count($collection) === 0) {
            $collection->add(new WaitAction($this, $enemyCommand, $alliesCommand, $this->container->getMessage()));
        }

        return $collection;
    }
}
