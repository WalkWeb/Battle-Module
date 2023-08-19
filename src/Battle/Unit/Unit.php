<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\WaitAction;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\Offense\OffenseInterface;
use Battle\Unit\Traits\ModifyStatsTrait;
use Exception;

class Unit extends AbstractUnit
{
    use ModifyStatsTrait;

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
    public function getActions(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        // Если юнит мертв или уже ходил - бросаем исключение
        if (!$this->isAlive() || $this->isAction()) {
            throw new UnitException(UnitException::CANNOT_ACTION);
        }

        // Если есть способность готовая к использованию - применяем её
        if (($ability = $this->getAbility($enemyCommand, $alliesCommand))) {
            $ability->usage();
            return $ability->getActions($enemyCommand, $alliesCommand);
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
     * @return AbilityCollection
     */
    public function getDeadAbilities(): AbilityCollection
    {
        $abilities = new AbilityCollection($this->container->isTestMode());

        if (!$this->isAlive()) {
            foreach ($this->abilities as $ability) {
                if ($ability->getTypeActivate() === AbilityInterface::ACTIVATE_DEAD && $ability->isReady()) {
                    $abilities->add($ability);
                }
            }
        }

        return $abilities;
    }

    /**
     * Принимает и обрабатывает абстрактное действие от другого юнита.
     *
     * @param ActionInterface $action
     * @return ActionCollection
     * @throws Exception
     * @uses applyDamageAction, applyHealAction, applyManaRestoreAction, applySummonAction, applyWaitAction, applyBuffAction, applyEffectAction, applyResurrectionAction, applyParalysisAction
     */
    public function applyAction(ActionInterface $action): ActionCollection
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
     * Позже, когда будет добавлен магический блок, игнорирование бока, уклонение, критические удары - все эти
     * механики будут вынесены в отдельный класс Calculator
     *
     * @param ActionInterface $action - ожидается DamageAction
     * @return ActionCollection
     * @throws Exception
     */
    private function applyDamageAction(ActionInterface $action): ActionCollection
    {
        if ($this->isEvaded($action)) {
            $action->addFactualPower($this, 0);
            $action->dodged($this);
            return new ActionCollection();
        }

        if ($this->isBlocked($action)) {
            $action->addFactualPower($this, 0);
            $action->blocked($this);
            return new ActionCollection();
        }

        $factualPower = $this->applyDamage($action);

        if ($this->life === 0) {

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

        $action->addFactualPower($this, $factualPower);

        // Для активации способностей, которые завязаны на уровень здоровья
        $this->abilities->update($this);

        // Если удар был критическим - необходимо создать коллекцию соответствующих событий от оружия
        // Применяться полученная коллекция будет в Stroke. Юнит должен просто сообщить, что нужно выполнить
        if ($action->isCriticalDamage()) {

            // TODO Нужен отдельный тест на проверку корректности команд, т.к. они меняются на противоположные
            return $action->getActionUnit()->getOffense()->getWeaponType()->getOnCriticalAction(
                $this,
                $action->getAlliesCommand(),
                $action->getEnemyCommand()
            );
        }

        if ($action->isTargetTracking()) {
            $action->getActionUnit()->addLastTarget($this);
        }

        return new ActionCollection();
    }

    /**
     * Обрабатывает action на лечение
     *
     * @param ActionInterface $action - ожидается HealAction
     * @return ActionCollection
     * @throws ActionException
     */
    private function applyHealAction(ActionInterface $action): ActionCollection
    {
        $primordialLife = $this->life;

        $this->life += $action->getPower();
        if ($this->life > $this->totalLife) {
            $this->life = $this->totalLife;
        }

        $action->addFactualPower($this, $this->life - $primordialLife);

        if ($action->isTargetTracking()) {
            $action->getActionUnit()->addLastTarget($this);
        }

        return new ActionCollection();
    }

    /**
     * Обрабатывает action на восстановление маны
     *
     * @param ActionInterface $action - ожидается ManaRestoreAction
     * @return ActionCollection
     * @throws ActionException
     */
    private function applyManaRestoreAction(ActionInterface $action): ActionCollection
    {
        $primordialMana = $this->mana;

        $this->mana += $action->getPower();
        if ($this->mana > $this->totalMana) {
            $this->mana = $this->totalMana;
        }

        $action->addFactualPower($this, $this->mana - $primordialMana);

        return new ActionCollection();
    }

    /**
     * Обрабатывает action на призыв нового юнита
     *
     * Призыв суммона в команду происходит в самом SummonAction, от юнита ничего не нужно
     *
     * @return ActionCollection
     */
    private function applySummonAction(): ActionCollection
    {
        return new ActionCollection();
    }

    /**
     * Обрабатывает action на пропуск хода
     *
     * @return ActionCollection
     */
    private function applyWaitAction(): ActionCollection
    {
        return new ActionCollection();
    }

    /**
     * Обрабатывает action на добавление эффекта юниту
     *
     * @param ActionInterface $action - ожидается EffectAction
     * @return ActionCollection
     * @throws ActionException
     */
    private function applyEffectAction(ActionInterface $action): ActionCollection
    {
        $onApplyAction = $this->effects->add($action->getEffect());

        foreach ($onApplyAction as $applyAction) {
            if ($applyAction->canByUsed()) {
                $applyAction->handle();
            }
        }

        return new ActionCollection();
    }

    /**
     * Обрабатывает action на воскрешение. При этом power - количество здоровья (в % от максимального), которое будет
     * восстановлено юниту при воскрешении
     *
     * @param ActionInterface $action - ожидается ResurrectionAction
     * @return ActionCollection
     * @throws ActionException
     */
    private function applyResurrectionAction(ActionInterface $action): ActionCollection
    {
        $restoreLife = (int)($this->totalLife * ($action->getPower()/100));

        // Если максимальное здоровье и power небольшие, то простое округление даст 0, соответственно делаем 1
        if ($restoreLife === 0) {
            $restoreLife = 1;
        }

        $this->life += $restoreLife;

        $action->addFactualPower($this, $restoreLife);

        return new ActionCollection();
    }

    /**
     * Обрабатывает action который обездвиживает цель и не дает ей ходить
     *
     * По факту просто указываем у юнита, что он ходил.
     *
     * @return ActionCollection
     */
    protected function applyParalysisAction(): ActionCollection
    {
        $this->action = true;
        return new ActionCollection();
    }

    /**
     * Обрабатывает action на изменение характеристик юнита
     *
     * При этом само событие указывает, в getModifyMethod(), какой метод должен обработать текущее изменение
     * характеристик
     *
     * @uses multiplierPhysicalDamage, multiplierPhysicalDamageRevert, multiplierMaxLife, multiplierMaxLifeRevert, multiplierAttackSpeed, multiplierAttackSpeedRevert, addBlock, addBlockRevert, multiplierAccuracy, multiplierAccuracyRevert, multiplierMagicAccuracy, multiplierMagicAccuracyRevert, multiplierDefense, multiplierDefenseRevert, multiplierMagicDefense, multiplierMagicDefenseRevert, multiplierCriticalChance, multiplierCriticalChanceRevert, multiplierCriticalMultiplier, multiplierCriticalMultiplierRevert, multiplierFireDamage, multiplierFireDamageRevert, multiplierWaterDamage, multiplierWaterDamageRevert, multiplierAirDamage, multiplierAirDamageRevert, multiplierEarthDamage, multiplierEarthDamageRevert, multiplierLifeDamage, multiplierLifeDamageRevert, multiplierDeathDamage, multiplierDeathDamageRevert, addPhysicalResist, addPhysicalResistRevert, addFireResist, addFireResistRevert, addWaterResist, addWaterResistRevert, addAirResist, addAirResistRevert, addEarthResist, addEarthResistRevert, addLifeResist, addLifeResistRevert, addDeathResist, addDeathResistRevert, addPhysicalMaxResist, addPhysicalMaxResistRevert, addFireMaxResist, addFireMaxResistRevert, addWaterMaxResist, addWaterMaxResistRevert, addAirMaxResist, addAirMaxResistRevert, addEarthMaxResist, addEarthMaxResistRevert, addLifeMaxResist, addLifeMaxResistRevert, addDeathMaxResist, addDeathMaxResistRevert
     * @param ActionInterface $action
     * @return ActionCollection
     * @throws ActionException
     * @throws UnitException
     */
    private function applyBuffAction(ActionInterface $action): ActionCollection
    {
        if (!method_exists($this, $modifyMethod = $action->getModifyMethod())) {
            throw new UnitException(UnitException::UNDEFINED_MODIFY_METHOD . ': ' . $modifyMethod);
        }

        $this->$modifyMethod($action);

        return new ActionCollection();
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
        $hits = $this->calculateHits();

        for ($i = 0; $i < $hits; $i++) {
            $collection->add(new DamageAction(
                $this->container,
                $this,
                $enemyCommand,
                $alliesCommand,
                DamageAction::TARGET_RANDOM_ENEMY,
                true,
                DamageAction::DEFAULT_NAME,
                DamageAction::UNIT_ANIMATION_METHOD,
                DamageAction::DEFAULT_MESSAGE_METHOD,
                $this->getOffense()
            ));
        }

        if (count($collection) === 0) {
            $collection->add(new WaitAction($this->container, $this, $enemyCommand, $alliesCommand));
        }

        return $collection;
    }

    /**
     * Рассчитывает вероятность юнита уклониться от получаемого удара
     *
     * В тестах мы не можем допустить случайности нанесения удара, по этому если бой работает в режиме теста, шанс
     * попадания определяется (т.е. не-уклонения) так: если шанс 50% или больше - всегда true, иначе всегда false
     *
     * TODO Позже, когда механики будут усложняться (пока рассчитывается только шанс блока и уклонения) все формулы
     * TODO будут вынесены в отдельный Calculator, это разгрузит данный класс от кода и сложности
     *
     * @param ActionInterface $action - ожидается DamageAction
     * @return bool
     * @throws Exception
     */
    private function isEvaded(ActionInterface $action): bool
    {
        if (!$action->isCanBeAvoided()) {
            return false;
        }

        if ($this->isParalysis()) {
            return false;
        }

        // Механика dodge (в отличие от механики defense) не зависит от меткости противника
        if ($this->defense->getDodge() > random_int(0, 100)) {
            return true;
        }

        $chanceOfHit = $this->getChanceOfHit($action);

        if ($this->container->isTestMode()) {
            return !(bool)(int)round($chanceOfHit/100);
        }

        return $chanceOfHit <= random_int(0, 100);
    }

    /**
     * Рассчитывает шанс попадания по текущему юниту
     *
     * TODO Брать меткость нападающего не от юнита, а от Offense
     *
     * @param ActionInterface $action - ожидается DamageAction
     * @return int
     * @throws Exception
     */
    private function getChanceOfHit(ActionInterface $action): int
    {
        // Если атака - используется обычная меткость и защита, если заклинание - магическая меткость и защита
        if ($action->getActionUnit()->getOffense()->getDamageType() === OffenseInterface::TYPE_ATTACK) {
            $accuracy = $action->getActionUnit()->getOffense()->getAccuracy();
            $defense = $this->defense->getDefense();
        } else {
            $accuracy = $action->getActionUnit()->getOffense()->getMagicAccuracy();
            $defense = $this->defense->getMagicDefense();
        }

        $chanceOfHit = (int)round(($accuracy - $defense) / ($accuracy / 10) * 2 + 80);

        // TODO Можно добавить шанс попадания в FullLog, для большей информативности логов
        // TODO Но реализовать одно сообщение при ударе нескольких целей - не так просто
        // TODO Как вариант - добавлять сообщение в FullLog не от сюда, а из чата
        //$this->container->getFullLog()->add('<p>Шанс попадания: ' . $chanceOfHit . '%</p>');

        if ($chanceOfHit < self::MIN_HIT_CHANCE) {
            return self::MIN_HIT_CHANCE;
        }

        if ($chanceOfHit > self::MAX_HIT_CHANCE) {
            return self::MAX_HIT_CHANCE;
        }

        return $chanceOfHit;
    }

    /**
     * @param ActionInterface $action - ожидается DamageAction
     * @return bool
     * @throws Exception
     */
    private function isBlocked(ActionInterface $action): bool
    {
        $block = $action->getActionUnit()->getOffense()->getDamageType() === OffenseInterface::TYPE_ATTACK ?
            $this->getDefense()->getBlock() : $this->getDefense()->getMagicBlock();

        if ($block === 0) {
            return false;
        }

        if (!$action->isCanBeAvoided()) {
            return false;
        }

        if ($this->isParalysis()) {
            return false;
        }

        return ($block - $action->getActionUnit()->getOffense()->getBlockIgnoring()) >= random_int(1, 100);
    }

    /**
     * Фактически наносит урон юниту и возвращает нанесенный урон: если у юнита 10 здоровья, а он получил 100 урона -
     * нанесенный урон будет 10
     *
     * Также в этом методе считается эффект ментального барьера - когда часть урона идет по мане
     *
     * @param ActionInterface $action
     * @return int
     * @throws Exception
     */
    private function applyDamage(ActionInterface $action): int
    {
        $oldLife = $this->life;
        $oldMana = $this->mana;

        $baseDamage = $action->getOffense()->getDamage($this->defense);

        $damage = $action->isCriticalDamage() ?
            (int)($baseDamage * ($this->getOffense()->getCriticalMultiplier() / 100)) :
            $baseDamage;

        if ($this->defense->getMentalBarrier() > 0) {
            $damageByMana = (int)($damage / (100 / $this->defense->getMentalBarrier()));
            $damageByLife = $damage - $damageByMana;
        } else {
            $damageByMana = 0;
            $damageByLife = $damage;
        }

        if ($damageByMana > 0) {

            $this->mana -= $damageByMana;

            if ($this->mana < 0) {
                $this->life += $this->mana;
                $this->mana = 0;
            }
        }

        $this->life -= $damageByLife;

        if ($this->life < 0) {
            $this->life = 0;
        }

        return $oldLife - $this->life + $oldMana - $this->mana;
    }
}
