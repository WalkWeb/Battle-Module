<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Action\BuffAction;
use Battle\Action\DamageAction;
use Battle\Action\EffectAction;
use Battle\Action\HealAction;
use Battle\Action\ResurrectionAction;
use Battle\Action\WaitAction;
use Battle\Action\SummonAction;
use Battle\Command\CommandInterface;
use Battle\Container\ContainerException;
use Battle\Result\Chat\ChatException;
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
     * @uses applyDamageAction, applyHealAction, applySummonAction, applyWaitAction, applyBuffAction, applyEffectAction, applyResurrectionAction
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
     * @throws ChatException
     */
    private function applyDamageAction(DamageAction $action): string
    {
        $primordialLife = $this->life;

        $this->life -= $action->getPower();

        if ($this->life < 1) {
            $this->life = 0;

            // Обрабатываем события от эффектов при смерти
            // TODO Когда будут добавлены сложные эффекты при смерти - нужно будет отдельно создавать сообщения в чат
            // TODO и анимации. Но для текущих простых эффектов это не нужно - ничего особенно при смерти они не создают
            $dieActions = $this->effects->diedParentUnit();

            foreach ($dieActions as $dieAction) {
                if ($dieAction->canByUsed()) {
                    $dieAction->handle();
                }
            }
        }

        $action->setFactualPower($primordialLife - $this->life);

        return $this->container->getChat()->addMessage($action);
    }

    /**
     * Обрабатывает action на лечение
     *
     * @param HealAction $action
     * @return string
     * @throws ActionException
     * @throws ContainerException
     * @throws ChatException
     */
    private function applyHealAction(HealAction $action): string
    {
        $primordialLife = $this->life;

        $this->life += $action->getPower();
        if ($this->life > $this->totalLife) {
            $this->life = $this->totalLife;
        }

        $action->setFactualPower($this->life - $primordialLife);

        return $this->container->getChat()->addMessage($action);
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
     * @throws ChatException
     */
    private function applySummonAction(SummonAction $action): string
    {
        return $this->container->getChat()->addMessage($action);
    }

    /**
     * Обрабатывает action на пропуск хода
     *
     * @param WaitAction $action
     * @return string
     * @throws ContainerException
     * @throws ChatException
     */
    private function applyWaitAction(WaitAction $action): string
    {
        return $this->container->getChat()->addMessage($action);
    }

    /**
     * Обрабатывает action на добавление эффекта юниту
     *
     * @param EffectAction $action
     * @return string
     * @throws ContainerException
     * @throws ActionException
     * @throws ChatException
     */
    private function applyEffectAction(EffectAction $action): string
    {
        $onApplyAction = $this->effects->add($action->getEffect());

        foreach ($onApplyAction as $applyAction) {
            if ($applyAction->canByUsed()) {
                $applyAction->handle();
            }
        }

        return $this->container->getChat()->addMessage($action);
    }

    /**
     * Обрабатывает action на воскрешение. При этом power - количество здоровья (в % от максимального), которое будет
     * восстановлено юниту при воскрешении
     *
     * @param ResurrectionAction $action
     * @return string
     * @throws ContainerException
     * @throws ActionException
     * @throws ChatException
     */
    private function applyResurrectionAction(ResurrectionAction $action): string
    {
        $restoreLife = (int)($this->totalLife * ($action->getPower()/100));

        // Если максимальное здоровье и power небольшие, то простое округление даст 0, соответственно делаем 1
        if ($restoreLife === 0) {
            $restoreLife = 1;
        }

        $this->life += $restoreLife;

        $action->setFactualPower($restoreLife);

        return $this->container->getChat()->addMessage($action);
    }
    
    /**
     * Обрабатывает action на изменение характеристик юнита
     *
     * При этом само событие указывает, в getModifyMethod(), какой метод должен обработать текущее изменение
     * характеристик
     *
     * @uses multiplierMaxLife, multiplierMaxLifeRevert
     * @param BuffAction $action
     * @return string
     * @throws ActionException
     * @throws UnitException
     */
    private function applyBuffAction(BuffAction $action): string
    {
        if (!method_exists($this, $modifyMethod = $action->getModifyMethod())) {
            throw new UnitException(UnitException::UNDEFINED_MODIFY_METHOD . ': ' . $modifyMethod);
        }

        return $this->$modifyMethod($action);
    }

    /**
     * Увеличивает здоровье юнита (можно сделать и уменьшение, но пока делаем только увеличение)
     *
     * @param BuffAction $action
     * @return string
     * @throws UnitException
     * @throws ActionException
     * @throws ContainerException
     * @throws ChatException
     */
    private function multiplierMaxLife(BuffAction $action): string
    {
        if ($action->getPower() <= 100) {
            throw new UnitException(UnitException::NO_REDUCED_LIFE_MULTIPLIER);
        }

        $multiplier = $action->getPower() / 100;

        $oldLife = $this->totalLife;
        $newHpMax = (int)($this->totalLife * $multiplier);

        $bonus = $newHpMax - $oldLife;

        $this->life += $bonus;
        $this->totalLife += $bonus;

        $action->setRevertValue($bonus);

        return $this->container->getChat()->addMessage($action);
    }

    /**
     * Откатывает изменения по здоровью
     *
     * @param BuffAction $action
     * @return string
     * @throws ActionException
     */
    private function multiplierMaxLifeRevert(BuffAction $action): string
    {
        $this->totalLife -= $action->getRevertValue();

        if ($this->life > $this->totalLife) {
            $this->life = $this->totalLife;
        }

        return '';
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
            $collection->add(new DamageAction(
                $this,
                $enemyCommand,
                $alliesCommand,
                DamageAction::TARGET_RANDOM_ENEMY
            ));
        }

        if (count($collection) === 0) {
            $collection->add(new WaitAction($this, $enemyCommand, $alliesCommand));
        }

        return $collection;
    }
}
