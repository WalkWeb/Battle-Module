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
        // Если есть способность готовая к использованию - применяем её
        if (($ability = $this->getAbility($enemyCommand, $alliesCommand))) {
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
     * @throws Exception
     */
    public function applyAction(ActionInterface $action): void
    {
        $method = $action->getHandleMethod();

        if (!method_exists($this, $method)) {
            throw new UnitException(UnitException::UNDEFINED_ACTION_METHOD);
        }

        $this->addConcentration(self::ADD_CON_RECEIVING_UNIT);
        $this->addRage(self::ADD_RAGE_RECEIVING_UNIT);

        $this->$method($action);
    }

    /**
     * Обрабатывает action на получение урона
     *
     * Позже, когда будет добавлен магический блок, игнорирование бока, уклонение, критические удары - все эти
     * механики будут вынесены в отдельный класс Calculator
     *
     * @param DamageAction $action
     * @throws Exception
     */
    private function applyDamageAction(DamageAction $action): void
    {
        // Проверка блока
        if ($this->block && ($this->block - $action->getBlockIgnore()) >= random_int(0, 100)) {
            $action->addFactualPower($this->id, 0);
            $action->blocked($this);
            return;
        }

        // Применение урона
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

        $action->addFactualPower($this->id, $primordialLife - $this->life);
    }

    /**
     * Обрабатывает action на лечение
     *
     * @param HealAction $action
     * @throws ActionException
     */
    private function applyHealAction(HealAction $action): void
    {
        $primordialLife = $this->life;

        $this->life += $action->getPower();
        if ($this->life > $this->totalLife) {
            $this->life = $this->totalLife;
        }

        $action->addFactualPower($this->id, $this->life - $primordialLife);
    }

    /**
     * Обрабатывает action на призыв нового юнита
     *
     * Призыв суммона в команду происходит в самом SummonAction, от юнита ничего не нужно
     *
     * @param SummonAction $action
     */
    private function applySummonAction(SummonAction $action): void {}

    /**
     * Обрабатывает action на пропуск хода
     *
     * @param WaitAction $action
     */
    private function applyWaitAction(WaitAction $action): void {}

    /**
     * Обрабатывает action на добавление эффекта юниту
     *
     * @param EffectAction $action
     * @throws ActionException
     */
    private function applyEffectAction(EffectAction $action): void
    {
        $onApplyAction = $this->effects->add($action->getEffect());

        foreach ($onApplyAction as $applyAction) {
            if ($applyAction->canByUsed()) {
                $applyAction->handle();
            }
        }
    }

    /**
     * Обрабатывает action на воскрешение. При этом power - количество здоровья (в % от максимального), которое будет
     * восстановлено юниту при воскрешении
     *
     * @param ResurrectionAction $action
     * @throws ActionException
     */
    private function applyResurrectionAction(ResurrectionAction $action): void
    {
        $restoreLife = (int)($this->totalLife * ($action->getPower()/100));

        // Если максимальное здоровье и power небольшие, то простое округление даст 0, соответственно делаем 1
        if ($restoreLife === 0) {
            $restoreLife = 1;
        }

        $this->life += $restoreLife;

        $action->addFactualPower($this->id, $restoreLife);
    }
    
    /**
     * Обрабатывает action на изменение характеристик юнита
     *
     * При этом само событие указывает, в getModifyMethod(), какой метод должен обработать текущее изменение
     * характеристик
     *
     * @uses multiplierMaxLife, multiplierMaxLifeRevert, multiplierAttackSpeed, multiplierAttackSpeedRevert
     * @param BuffAction $action
     * @throws ActionException
     * @throws UnitException
     */
    private function applyBuffAction(BuffAction $action): void
    {
        if (!method_exists($this, $modifyMethod = $action->getModifyMethod())) {
            throw new UnitException(UnitException::UNDEFINED_MODIFY_METHOD . ': ' . $modifyMethod);
        }

        $this->$modifyMethod($action);
    }

    /**
     * Увеличивает здоровье юнита (можно сделать и уменьшение, но пока делаем только увеличение)
     *
     * @param BuffAction $action
     * @throws UnitException
     * @throws ActionException
     */
    private function multiplierMaxLife(BuffAction $action): void
    {
        if ($action->getPower() <= 100) {
            throw new UnitException(UnitException::NO_REDUCED_MAXIMUM_LIFE);
        }

        $multiplier = $action->getPower() / 100;

        $oldLife = $this->totalLife;
        $newHpMax = (int)($this->totalLife * $multiplier);

        $bonus = $newHpMax - $oldLife;

        $this->life += $bonus;
        $this->totalLife += $bonus;

        $action->setRevertValue($bonus);
    }

    /**
     * Откатывает изменения по здоровью
     *
     * @param BuffAction $action
     * @throws ActionException
     */
    private function multiplierMaxLifeRevert(BuffAction $action): void
    {
        $this->totalLife -= $action->getRevertValue();

        if ($this->life > $this->totalLife) {
            $this->life = $this->totalLife;
        }
    }

    /**
     * Увеличивает скорость атаки юнита
     *
     * @param BuffAction $action
     * @throws ActionException
     * @throws UnitException
     */
    private function multiplierAttackSpeed(BuffAction $action): void
    {
        if ($action->getPower() <= 100) {
            throw new UnitException(UnitException::NO_REDUCED_ATTACK_SPEED);
        }

        $multiplier = $action->getPower() / 100;

        $oldAttackSpeed = $this->attackSpeed;
        $newAttackSpeed = $this->attackSpeed * $multiplier;

        $bonus = $newAttackSpeed - $oldAttackSpeed;

        $this->attackSpeed += $bonus;

        $action->setRevertValue($bonus);
    }

    /**
     * Откатывает обратно увеличенную скорость атаки
     *
     * @param BuffAction $action
     * @throws ActionException
     */
    private function multiplierAttackSpeedRevert(BuffAction $action): void
    {
        $this->attackSpeed -= $action->getRevertValue();
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
                DamageAction::TARGET_RANDOM_ENEMY,
                $this->getDamage(),
                $this->getBlockIgnore()
            ));
        }

        if (count($collection) === 0) {
            $collection->add(new WaitAction($this, $enemyCommand, $alliesCommand));
        }

        return $collection;
    }
}
