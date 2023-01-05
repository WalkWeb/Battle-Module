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
use Battle\Unit\Defense\DefenseException;
use Battle\Unit\Defense\DefenseInterface;
use Battle\Unit\Offense\OffenseException;
use Battle\Unit\Offense\OffenseInterface;
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
     * @uses applyDamageAction, applyHealAction, applySummonAction, applyWaitAction, applyBuffAction, applyEffectAction, applyResurrectionAction, applyParalysisAction
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
     * @param ActionInterface $action - ожидается DamageAction
     * @throws Exception
     */
    private function applyDamageAction(ActionInterface $action): void
    {
        if ($this->isDodged($action)) {
            $action->addFactualPower($this, 0);
            $action->dodged($this);
            return;
        }

        if ($this->isBlocked($action)) {
            $action->addFactualPower($this, 0);
            $action->blocked($this);
            return;
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
    }

    /**
     * Обрабатывает action на лечение
     *
     * @param ActionInterface $action - ожидается HealAction
     * @throws ActionException
     */
    private function applyHealAction(ActionInterface $action): void
    {
        $primordialLife = $this->life;

        $this->life += $action->getPower();
        if ($this->life > $this->totalLife) {
            $this->life = $this->totalLife;
        }

        $action->addFactualPower($this, $this->life - $primordialLife);
    }

    /**
     * Обрабатывает action на призыв нового юнита
     *
     * Призыв суммона в команду происходит в самом SummonAction, от юнита ничего не нужно
     *
     * @param ActionInterface $action
     */
    private function applySummonAction(ActionInterface $action): void {}

    /**
     * Обрабатывает action на пропуск хода
     *
     * @param ActionInterface $action
     */
    private function applyWaitAction(ActionInterface $action): void {}

    /**
     * Обрабатывает action на добавление эффекта юниту
     *
     * @param ActionInterface $action - ожидается EffectAction
     * @throws ActionException
     */
    private function applyEffectAction(ActionInterface $action): void
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
     * @param ActionInterface $action - ожидается ResurrectionAction
     * @throws ActionException
     */
    private function applyResurrectionAction(ActionInterface $action): void
    {
        $restoreLife = (int)($this->totalLife * ($action->getPower()/100));

        // Если максимальное здоровье и power небольшие, то простое округление даст 0, соответственно делаем 1
        if ($restoreLife === 0) {
            $restoreLife = 1;
        }

        $this->life += $restoreLife;

        $action->addFactualPower($this, $restoreLife);
    }

    /**
     * Обрабатывает action который обездвиживает цель и не дает ей ходить
     *
     * По факту просто указываем у юнита, что он ходил.
     */
    protected function applyParalysisAction(): void
    {
        $this->action = true;
    }

    /**
     * Обрабатывает action на изменение характеристик юнита
     *
     * При этом само событие указывает, в getModifyMethod(), какой метод должен обработать текущее изменение
     * характеристик
     *
     * @uses multiplierPhysicalDamage, multiplierPhysicalDamageRevert, multiplierMaxLife, multiplierMaxLifeRevert, multiplierAttackSpeed, multiplierAttackSpeedRevert, addBlock, addBlockRevert
     * @param ActionInterface $action - ожидается BuffAction
     * @throws ActionException
     * @throws UnitException
     */
    private function applyBuffAction(ActionInterface $action): void
    {
        if (!method_exists($this, $modifyMethod = $action->getModifyMethod())) {
            throw new UnitException(UnitException::UNDEFINED_MODIFY_METHOD . ': ' . $modifyMethod);
        }

        $this->$modifyMethod($action);
    }

    /**
     * Увеличивает урон юнита (можно сделать и уменьшение, но пока делаем только увеличение)
     *
     * @param ActionInterface $action - ожидается BuffAction
     * @throws ActionException
     * @throws UnitException
     * @throws OffenseException
     */
    private function multiplierPhysicalDamage(ActionInterface $action): void
    {
        if ($action->getPower() <= 100) {
            throw new UnitException(UnitException::NO_REDUCED_DAMAGE);
        }

        $multiplier = $action->getPower() / 100;

        $oldDamage = $this->offense->getPhysicalDamage();
        $newDamage = (int)($this->offense->getPhysicalDamage() * $multiplier);

        $bonus = $newDamage - $oldDamage;

        $this->offense->setPhysicalDamage($newDamage);

        $action->setRevertValue($bonus);
    }

    /**
     * Откатывает изменение урона юнита
     *
     * @param ActionInterface $action - ожидается BuffAction
     * @throws ActionException
     * @throws OffenseException
     */
    private function multiplierPhysicalDamageRevert(ActionInterface $action): void
    {
        $this->offense->setPhysicalDamage($this->offense->getPhysicalDamage() - $action->getRevertValue());
    }

    /**
     * Увеличивает здоровье юнита (можно сделать и уменьшение, но пока делаем только увеличение)
     *
     * @param ActionInterface $action - ожидается BuffAction
     * @throws UnitException
     * @throws ActionException
     */
    private function multiplierMaxLife(ActionInterface $action): void
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
     * @param ActionInterface $action - ожидается BuffAction
     * @throws ActionException
     */
    private function multiplierMaxLifeRevert(ActionInterface $action): void
    {
        $this->totalLife -= $action->getRevertValue();

        if ($this->life > $this->totalLife) {
            $this->life = $this->totalLife;
        }
    }

    /**
     * Увеличивает скорость атаки юнита
     *
     * @param ActionInterface $action - ожидается BuffAction
     * @throws ActionException
     * @throws OffenseException
     * @throws UnitException
     */
    private function multiplierAttackSpeed(ActionInterface $action): void
    {
        if ($action->getPower() <= 100) {
            throw new UnitException(UnitException::NO_REDUCED_ATTACK_SPEED);
        }

        $multiplier = $action->getPower() / 100;

        $attackSpeed = $this->offense->getAttackSpeed();
        $oldAttackSpeed = $attackSpeed;
        $newAttackSpeed = $attackSpeed * $multiplier;

        $bonus = $newAttackSpeed - $oldAttackSpeed;

        $this->offense->setAttackSpeed($newAttackSpeed);
        $action->setRevertValue($bonus);
    }

    /**
     * Откатывает обратно увеличенную скорость атаки
     *
     * @param ActionInterface $action - ожидается BuffAction
     * @throws ActionException
     * @throws OffenseException
     */
    private function multiplierAttackSpeedRevert(ActionInterface $action): void
    {
        $attackSpeed = $this->offense->getAttackSpeed();
        $attackSpeed -= $action->getRevertValue();
        $this->offense->setAttackSpeed($attackSpeed);
    }

    /**
     * Увеличивает блок юнита на фиксированную величину
     *
     * @param ActionInterface $action - ожидается BuffAction
     * @throws ActionException
     * @throws DefenseException
     */
    private function addBlock(ActionInterface $action): void
    {
        $oldBlock = $block = $this->defense->getBlock();
        $block += $action->getPower();

        if ($block > DefenseInterface::MAX_BLOCK) {
            $block = DefenseInterface::MAX_BLOCK;
        }

        $this->defense->setBlock($block);
        $action->setRevertValue($block - $oldBlock);
    }

    /**
     * Возвращает блок юнита к исходному значению
     *
     * @param ActionInterface $action - ожидается BuffAction
     * @throws ActionException
     * @throws DefenseException
     */
    private function addBlockRevert(ActionInterface $action): void
    {
        $block = $this->defense->getBlock();
        $block -= $action->getRevertValue();
        $this->defense->setBlock($block);
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
                $this->getOffense(),
                true,
                DamageAction::DEFAULT_NAME,
                DamageAction::UNIT_ANIMATION_METHOD,
                DamageAction::DEFAULT_MESSAGE_METHOD
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
    private function isDodged(ActionInterface $action): bool
    {
        if (!$action->isCanBeAvoided()) {
            return false;
        }

        if ($this->isParalysis()) {
            return false;
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
