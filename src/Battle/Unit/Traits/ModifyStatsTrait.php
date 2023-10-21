<?php

declare(strict_types=1);

namespace Battle\Unit\Traits;

use Battle\Action\ActionInterface;
use Battle\Unit\Defense\DefenseInterface;
use Battle\Unit\Offense\OffenseInterface;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;
use Exception;

/**
 * В этом трейте хранятся однотипные (в основном) методы на изменение характеристик юнита (и откат изменений)
 *
 * Изменения всех характеристик можно сделать одним магическим (в смысле навороченным) методом, который на основании
 * названия свойства юнита будет находить его и изменять (при этом часть будет в классе Unit, часть в Offense, а часть в
 * Defense.
 *
 * Но делаются отдельные методы на каждый параметр, чтобы через покрытие авто-тестами можно было убедиться, что
 * изменения всех параметров покрыты тестами.
 *
 * @package Battle\Unit\Traits
 */
trait ModifyStatsTrait
{
    /**
     * Изменяет меткость
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierAccuracy(ActionInterface $action): void
    {
        $oldAccuracy = $this->offense->getAccuracy();
        $newAccuracy = (int)($this->offense->getAccuracy() * $this->getMultiplier($action));

        $this->offense->setAccuracy($newAccuracy);

        $action->setRevertValue($newAccuracy - $oldAccuracy);
    }

    /**
     * Откатывает изменение меткости
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierAccuracyRevert(ActionInterface $action): void
    {
        $this->offense->setAccuracy($this->offense->getAccuracy() - $action->getRevertValue());
    }

    /**
     * Изменяет магическую меткость
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierMagicAccuracy(ActionInterface $action): void
    {
        $oldMagicAccuracy = $this->offense->getMagicAccuracy();
        $newMagicAccuracy = (int)($this->offense->getMagicAccuracy() * $this->getMultiplier($action));

        $this->offense->setMagicAccuracy($newMagicAccuracy);

        $action->setRevertValue($newMagicAccuracy - $oldMagicAccuracy);
    }

    /**
     * Откатывает изменение магической меткости
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierMagicAccuracyRevert(ActionInterface $action): void
    {
        $this->offense->setMagicAccuracy($this->offense->getMagicAccuracy() - $action->getRevertValue());
    }

    /**
     * Изменяет защиту
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierDefense(ActionInterface $action): void
    {
        $oldDefense = $this->defense->getDefense();
        $newDefense = (int)($this->defense->getDefense() * $this->getMultiplier($action));

        $this->defense->setDefense($newDefense);

        $action->setRevertValue($newDefense - $oldDefense);
    }

    /**
     * Откатывает изменение защиты
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierDefenseRevert(ActionInterface $action): void
    {
        $this->defense->setDefense($this->defense->getDefense() - $action->getRevertValue());
    }

    /**
     * Изменяет магическую защиту
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierMagicDefense(ActionInterface $action): void
    {
        $oldMagicDefense = $this->defense->getMagicDefense();
        $newMagicDefense = (int)($this->defense->getMagicDefense() * $this->getMultiplier($action));

        $this->defense->setMagicDefense($newMagicDefense);

        $action->setRevertValue($newMagicDefense - $oldMagicDefense);
    }

    /**
     * Откатывает изменение магической защиты
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierMagicDefenseRevert(ActionInterface $action): void
    {
        $this->defense->setMagicDefense($this->defense->getMagicDefense() - $action->getRevertValue());
    }

    /**
     * Увеличивает здоровье
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierMaxLife(ActionInterface $action): void
    {
        $multiplier = $this->getMultiplier($action);

        $oldMaxLife = $this->totalLife;
        $newMaxLife = (int)($this->totalLife * $multiplier);

        if ($newMaxLife < 1) {
            $newMaxLife = 1;
        }

        $bonus = $newMaxLife - $oldMaxLife;

        $this->totalLife += $bonus;

        if ($multiplier > 1) {
            $this->life += $bonus;
        }

        if ($this->life > $this->totalLife) {
            $this->life = $this->totalLife;
        }

        $action->setRevertValue($bonus);
    }

    /**
     * Откатывает изменения по здоровью
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierMaxLifeRevert(ActionInterface $action): void
    {
        $this->totalLife -= $action->getRevertValue();

        if ($this->life > $this->totalLife) {
            $this->life = $this->totalLife;
        }
    }

    /**
     * Изменяет максимальный запас маны
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierMaxMana(ActionInterface $action): void
    {
        $multiplier = $this->getMultiplier($action);

        $oldMaxMana = $this->totalMana;
        $newMaxMana = (int)($this->totalMana * $multiplier);

        if ($newMaxMana < 1) {
            $newMaxMana = 1;
        }

        $bonus = $newMaxMana - $oldMaxMana;

        $this->totalMana += $bonus;

        if ($multiplier > 1) {
            $this->mana += $bonus;
        }

        if ($this->mana > $this->totalMana) {
            $this->mana = $this->totalMana;
        }

        $action->setRevertValue($bonus);
    }

    /**
     * Откатывает изменения максимальной маны
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierMaxManaRevert(ActionInterface $action): void
    {
        $this->totalMana -= $action->getRevertValue();

        if ($this->mana > $this->totalMana) {
            $this->mana = $this->totalMana;
        }
    }

    /**
     * Увеличивает скорость атаки
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierAttackSpeed(ActionInterface $action): void
    {
        $oldAttackSpeed = $this->offense->getAttackSpeed();
        $newAttackSpeed = $oldAttackSpeed * $this->getMultiplier($action);

        $this->offense->setAttackSpeed($newAttackSpeed);

        $action->setRevertValue($newAttackSpeed - $oldAttackSpeed);
    }

    /**
     * Откатывает обратно увеличенную скорость атаки
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierAttackSpeedRevert(ActionInterface $action): void
    {
        $attackSpeed = $this->offense->getAttackSpeed();
        $attackSpeed -= $action->getRevertValue();
        $this->offense->setAttackSpeed($attackSpeed);
    }

    /**
     * Увеличивает блок на фиксированную величину
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addBlock(ActionInterface $action): void
    {
        $oldBlock = $block = $this->defense->getBlock();
        $block += $action->getPower();

        if ($block > DefenseInterface::MAX_BLOCK) {
            $block = DefenseInterface::MAX_BLOCK;
        }

        if ($block < DefenseInterface::MIN_BLOCK) {
            $block = DefenseInterface::MIN_BLOCK;
        }

        $this->defense->setBlock($block);
        $action->setRevertValue($block - $oldBlock);
    }

    /**
     * Возвращает блок к исходному значению
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addBlockRevert(ActionInterface $action): void
    {
        $this->defense->setBlock($this->defense->getBlock() - $action->getRevertValue());
    }

    /**
     * Изменяет магический блок на фиксированную величину
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addMagicBlock(ActionInterface $action): void
    {
        $oldMagicBlock = $this->defense->getMagicBlock();
        $newMagicBlock = $oldMagicBlock + $action->getPower();

        if ($newMagicBlock > DefenseInterface::MAX_BLOCK) {
            $newMagicBlock = DefenseInterface::MAX_BLOCK;
        }

        if ($newMagicBlock < DefenseInterface::MIN_BLOCK) {
            $newMagicBlock = DefenseInterface::MIN_BLOCK;
        }

        $this->defense->setMagicBlock($newMagicBlock);
        $action->setRevertValue($newMagicBlock - $oldMagicBlock);
    }

    /**
     * Откатывает изменение магического блока
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addMagicBlockRevert(ActionInterface $action): void
    {
        $this->defense->setMagicBlock($this->defense->getMagicBlock() - $action->getRevertValue());
    }

    /**
     * Изменяет шанс критического удара
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierCriticalChance(ActionInterface $action): void
    {
        if ($action->getPower() <= ActionInterface::MIN_MULTIPLIER) {
            throw new UnitException(UnitException::OVER_REDUCED . ActionInterface::MIN_MULTIPLIER);
        }

        $multiplier = $action->getPower() / 100;

        $oldCriticalChance = $this->offense->getCriticalChance();
        $newCriticalChance = (int)($this->offense->getCriticalChance() * $multiplier);

        $this->offense->setCriticalChance($newCriticalChance);

        $action->setRevertValue($newCriticalChance - $oldCriticalChance);
    }

    /**
     * Откатывает изменение шанса критического удара
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierCriticalChanceRevert(ActionInterface $action): void
    {
        $this->offense->setCriticalChance($this->offense->getCriticalChance() - $action->getRevertValue());
    }

    /**
     * Изменяет силу критического удара
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierCriticalMultiplier(ActionInterface $action): void
    {
        if ($action->getPower() <= ActionInterface::MIN_MULTIPLIER) {
            throw new UnitException(UnitException::OVER_REDUCED . ActionInterface::MIN_MULTIPLIER);
        }

        $multiplier = $action->getPower() / 100;

        $oldCriticalMultiplier = $this->offense->getCriticalMultiplier();
        $newCriticalMultiplier = (int)($this->offense->getCriticalMultiplier() * $multiplier);

        $this->offense->setCriticalMultiplier($newCriticalMultiplier);

        $action->setRevertValue($newCriticalMultiplier - $oldCriticalMultiplier);
    }

    /**
     * Откатывает изменение силы критического удара
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierCriticalMultiplierRevert(ActionInterface $action): void
    {
        $this->offense->setCriticalMultiplier($this->offense->getCriticalMultiplier() - $action->getRevertValue());
    }

    /**
     * Изменяет общий множитель урона
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addDamageMultiplier(ActionInterface $action): void
    {
        $oldDamageMultiplier = $this->offense->getDamageMultiplier();
        $newDamageMultiplier = (int)($this->offense->getDamageMultiplier() + $action->getPower());

        if ($newDamageMultiplier > OffenseInterface::MAX_DAMAGE_MULTIPLIER) {
            $newDamageMultiplier =  OffenseInterface::MAX_DAMAGE_MULTIPLIER;
        }

        if ($newDamageMultiplier < OffenseInterface::MIN_DAMAGE_MULTIPLIER) {
            $newDamageMultiplier =  OffenseInterface::MIN_DAMAGE_MULTIPLIER;
        }

        $this->offense->setDamageMultiplier($newDamageMultiplier);

        $action->setRevertValue($newDamageMultiplier - $oldDamageMultiplier);
    }

    /**
     * Откатывает изменение общего множителя урона
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addDamageMultiplierRevert(ActionInterface $action): void
    {
        $this->offense->setDamageMultiplier($this->offense->getDamageMultiplier() - $action->getRevertValue());
    }

    /**
     * Изменяет физический урон
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierPhysicalDamage(ActionInterface $action): void
    {
        $oldPhysicalDamage = $this->offense->getPhysicalDamage();
        $newPhysicalDamage = (int)($this->offense->getPhysicalDamage() * $this->getMultiplier($action));

        $this->offense->setPhysicalDamage($newPhysicalDamage);

        $action->setRevertValue($newPhysicalDamage - $oldPhysicalDamage);
    }

    /**
     * Откатывает изменение физического урона
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierPhysicalDamageRevert(ActionInterface $action): void
    {
        $this->offense->setPhysicalDamage($this->offense->getPhysicalDamage() - $action->getRevertValue());
    }

    /**
     * Изменяет урон огнем
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierFireDamage(ActionInterface $action): void
    {
        $oldFireDamage = $this->offense->getFireDamage();
        $newFireDamage = (int)($this->offense->getFireDamage() * $this->getMultiplier($action));

        $this->offense->setFireDamage($newFireDamage);

        $action->setRevertValue($newFireDamage - $oldFireDamage);
    }

    /**
     * Откатывает изменение урона огнем
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierFireDamageRevert(ActionInterface $action): void
    {
        $this->offense->setFireDamage($this->offense->getFireDamage() - $action->getRevertValue());
    }

    /**
     * Изменяет урон водой
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierWaterDamage(ActionInterface $action): void
    {
        if ($action->getPower() <= ActionInterface::MIN_MULTIPLIER) {
            throw new UnitException(UnitException::OVER_REDUCED . ActionInterface::MIN_MULTIPLIER);
        }

        $multiplier = $action->getPower() / 100;

        $oldWaterDamage = $this->offense->getWaterDamage();
        $newWaterDamage = (int)($this->offense->getWaterDamage() * $multiplier);

        $this->offense->setWaterDamage($newWaterDamage);

        $action->setRevertValue($newWaterDamage - $oldWaterDamage);
    }

    /**
     * Откатывает изменение урона водой
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierWaterDamageRevert(ActionInterface $action): void
    {
        $this->offense->setWaterDamage($this->offense->getWaterDamage() - $action->getRevertValue());
    }

    /**
     * Изменяет урон воздухом
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierAirDamage(ActionInterface $action): void
    {
        if ($action->getPower() <= ActionInterface::MIN_MULTIPLIER) {
            throw new UnitException(UnitException::OVER_REDUCED . ActionInterface::MIN_MULTIPLIER);
        }

        $multiplier = $action->getPower() / 100;

        $oldAirDamage = $this->offense->getAirDamage();
        $newAirDamage = (int)($this->offense->getAirDamage() * $multiplier);

        $this->offense->setAirDamage($newAirDamage);

        $action->setRevertValue($newAirDamage - $oldAirDamage);
    }

    /**
     * Откатывает изменение урона воздухом
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierAirDamageRevert(ActionInterface $action): void
    {
        $this->offense->setAirDamage($this->offense->getAirDamage() - $action->getRevertValue());
    }

    /**
     * Изменяет урон землей
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierEarthDamage(ActionInterface $action): void
    {
        if ($action->getPower() <= ActionInterface::MIN_MULTIPLIER) {
            throw new UnitException(UnitException::OVER_REDUCED . ActionInterface::MIN_MULTIPLIER);
        }

        $multiplier = $action->getPower() / 100;

        $oldEarthDamage = $this->offense->getEarthDamage();
        $newEarthDamage = (int)($this->offense->getEarthDamage() * $multiplier);

        $this->offense->setEarthDamage($newEarthDamage);

        $action->setRevertValue($newEarthDamage - $oldEarthDamage);
    }

    /**
     * Откатывает изменение урона землей
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierEarthDamageRevert(ActionInterface $action): void
    {
        $this->offense->setEarthDamage($this->offense->getEarthDamage() - $action->getRevertValue());
    }

    /**
     * Изменяет урон магией жизни
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierLifeDamage(ActionInterface $action): void
    {
        if ($action->getPower() <= ActionInterface::MIN_MULTIPLIER) {
            throw new UnitException(UnitException::OVER_REDUCED . ActionInterface::MIN_MULTIPLIER);
        }

        $multiplier = $action->getPower() / 100;

        $oldLifeDamage = $this->offense->getLifeDamage();
        $newLifeDamage = (int)($this->offense->getLifeDamage() * $multiplier);

        $this->offense->setLifeDamage($newLifeDamage);

        $action->setRevertValue($newLifeDamage - $oldLifeDamage);
    }

    /**
     * Откатывает изменение урона магией жизни
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierLifeDamageRevert(ActionInterface $action): void
    {
        $this->offense->setLifeDamage($this->offense->getLifeDamage() - $action->getRevertValue());
    }

    /**
     * Изменяет урон магией смерти
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierDeathDamage(ActionInterface $action): void
    {
        if ($action->getPower() <= ActionInterface::MIN_MULTIPLIER) {
            throw new UnitException(UnitException::OVER_REDUCED . ActionInterface::MIN_MULTIPLIER);
        }

        $multiplier = $action->getPower() / 100;

        $oldDeathDamage = $this->offense->getDeathDamage();
        $newDeathDamage = (int)($this->offense->getDeathDamage() * $multiplier);

        $this->offense->setDeathDamage($newDeathDamage);

        $action->setRevertValue($newDeathDamage - $oldDeathDamage);
    }

    /**
     * Откатывает изменение урона магией смерти
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierDeathDamageRevert(ActionInterface $action): void
    {
        $this->offense->setDeathDamage($this->offense->getDeathDamage() - $action->getRevertValue());
    }

    /**
     * Изменяет сопротивление физическому урону
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addPhysicalResist(ActionInterface $action): void
    {
        $oldPhysicalResist = $this->defense->getPhysicalResist();
        $newPhysicalResist = $this->defense->getPhysicalResist() + $action->getPower();

        if ($newPhysicalResist > $this->defense->getPhysicalMaxResist()) {
            $newPhysicalResist = $this->defense->getPhysicalMaxResist();
        }

        if ($newPhysicalResist < DefenseInterface::MIN_RESISTANCE) {
            $newPhysicalResist = DefenseInterface::MIN_RESISTANCE;
        }

        $this->defense->setPhysicalResist($newPhysicalResist);
        $action->setRevertValue($newPhysicalResist - $oldPhysicalResist);
    }

    /**
     * Откатывает изменение сопротивление физическому урону
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addPhysicalResistRevert(ActionInterface $action): void
    {
        $this->defense->setPhysicalResist($this->defense->getPhysicalResist() - $action->getRevertValue());
    }

    /**
     * Изменяет сопротивление урону огнем
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addFireResist(ActionInterface $action): void
    {
        $oldResist = $this->defense->getFireResist();
        $newResist = $this->defense->getFireResist() + $action->getPower();

        if ($newResist > $this->defense->getFireMaxResist()) {
            $newResist = $this->defense->getFireMaxResist();
        }

        if ($newResist < DefenseInterface::MIN_RESISTANCE) {
            $newResist = DefenseInterface::MIN_RESISTANCE;
        }

        $this->defense->setFireResist($newResist);
        $action->setRevertValue($newResist - $oldResist);
    }

    /**
     * Откатывает изменение сопротивление урону огнем
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addFireResistRevert(ActionInterface $action): void
    {
        $this->defense->setFireResist($this->defense->getFireResist() - $action->getRevertValue());
    }

    /**
     * Изменяет сопротивление урону водой
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addWaterResist(ActionInterface $action): void
    {
        $oldResist = $this->defense->getWaterResist();
        $newResist = $this->defense->getWaterResist() + $action->getPower();

        if ($newResist > $this->defense->getWaterMaxResist()) {
            $newResist = $this->defense->getWaterMaxResist();
        }

        if ($newResist < DefenseInterface::MIN_RESISTANCE) {
            $newResist = DefenseInterface::MIN_RESISTANCE;
        }

        $this->defense->setWaterResist($newResist);
        $action->setRevertValue($newResist - $oldResist);
    }

    /**
     * Откатывает изменение сопротивление урону водой
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addWaterResistRevert(ActionInterface $action): void
    {
        $this->defense->setWaterResist($this->defense->getWaterResist() - $action->getRevertValue());
    }

    /**
     * Изменяет сопротивление урону воздухом
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addAirResist(ActionInterface $action): void
    {
        $oldResist = $this->defense->getAirResist();
        $newResist = $this->defense->getAirResist() + $action->getPower();

        if ($newResist > $this->defense->getAirMaxResist()) {
            $newResist = $this->defense->getAirMaxResist();
        }

        if ($newResist < DefenseInterface::MIN_RESISTANCE) {
            $newResist = DefenseInterface::MIN_RESISTANCE;
        }

        $this->defense->setAirResist($newResist);
        $action->setRevertValue($newResist - $oldResist);
    }

    /**
     * Откатывает изменение сопротивление урону воздухом
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addAirResistRevert(ActionInterface $action): void
    {
        $this->defense->setAirResist($this->defense->getAirResist() - $action->getRevertValue());
    }

    /**
     * Изменяет сопротивление урону воздухом
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addEarthResist(ActionInterface $action): void
    {
        $oldResist = $this->defense->getEarthResist();
        $newResist = $this->defense->getEarthResist() + $action->getPower();

        if ($newResist > $this->defense->getEarthMaxResist()) {
            $newResist = $this->defense->getEarthMaxResist();
        }

        if ($newResist < DefenseInterface::MIN_RESISTANCE) {
            $newResist = DefenseInterface::MIN_RESISTANCE;
        }

        $this->defense->setEarthResist($newResist);
        $action->setRevertValue($newResist - $oldResist);
    }

    /**
     * Откатывает изменение сопротивление урону воздухом
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addEarthResistRevert(ActionInterface $action): void
    {
        $this->defense->setEarthResist($this->defense->getEarthResist() - $action->getRevertValue());
    }

    /**
     * Изменяет сопротивление урону воздухом
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addLifeResist(ActionInterface $action): void
    {
        $oldResist = $this->defense->getLifeResist();
        $newResist = $this->defense->getLifeResist() + $action->getPower();

        if ($newResist > $this->defense->getLifeMaxResist()) {
            $newResist = $this->defense->getLifeMaxResist();
        }

        if ($newResist < DefenseInterface::MIN_RESISTANCE) {
            $newResist = DefenseInterface::MIN_RESISTANCE;
        }

        $this->defense->setLifeResist($newResist);
        $action->setRevertValue($newResist - $oldResist);
    }

    /**
     * Откатывает изменение сопротивление урону воздухом
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addLifeResistRevert(ActionInterface $action): void
    {
        $this->defense->setLifeResist($this->defense->getLifeResist() - $action->getRevertValue());
    }

    /**
     * Изменяет сопротивление урону воздухом
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addDeathResist(ActionInterface $action): void
    {
        $oldResist = $this->defense->getDeathResist();
        $newResist = $this->defense->getDeathResist() + $action->getPower();

        if ($newResist > $this->defense->getDeathMaxResist()) {
            $newResist = $this->defense->getDeathMaxResist();
        }

        if ($newResist < DefenseInterface::MIN_RESISTANCE) {
            $newResist = DefenseInterface::MIN_RESISTANCE;
        }

        $this->defense->setDeathResist($newResist);
        $action->setRevertValue($newResist - $oldResist);
    }

    /**
     * Откатывает изменение сопротивление урону воздухом
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addDeathResistRevert(ActionInterface $action): void
    {
        $this->defense->setDeathResist($this->defense->getDeathResist() - $action->getRevertValue());
    }

    /**
     * Изменяет максимальное сопротивление физическому урону
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addPhysicalMaxResist(ActionInterface $action): void
    {
        $oldMaxResist = $this->defense->getPhysicalMaxResist();
        $newMaxResist = $this->defense->getPhysicalMaxResist() + $action->getPower();

        if ($newMaxResist > DefenseInterface::MAX_RESISTANCE) {
            $newMaxResist = DefenseInterface::MAX_RESISTANCE;
        }

        $this->defense->setPhysicalMaxResist($newMaxResist);
        $action->setRevertValue($newMaxResist - $oldMaxResist);
    }

    /**
     * Откатывает изменения максимального сопротивления физическому урону
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addPhysicalMaxResistRevert(ActionInterface $action): void
    {
        $this->defense->setPhysicalMaxResist($this->defense->getPhysicalMaxResist() - $action->getRevertValue());
    }

    /**
     * Изменяет максимальное сопротивление урону огнем
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addFireMaxResist(ActionInterface $action): void
    {
        $oldMaxResist = $this->defense->getFireMaxResist();
        $newMaxResist = $this->defense->getFireMaxResist() + $action->getPower();

        if ($newMaxResist > DefenseInterface::MAX_RESISTANCE) {
            $newMaxResist = DefenseInterface::MAX_RESISTANCE;
        }

        $this->defense->setFireMaxResist($newMaxResist);
        $action->setRevertValue($newMaxResist - $oldMaxResist);
    }

    /**
     * Откатывает изменения максимального сопротивления урону огнем
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addFireMaxResistRevert(ActionInterface $action): void
    {
        $this->defense->setFireMaxResist($this->defense->getFireMaxResist() - $action->getRevertValue());
    }

    /**
     * Изменяет максимальное сопротивление урону водой
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addWaterMaxResist(ActionInterface $action): void
    {
        $oldMaxResist = $this->defense->getWaterMaxResist();
        $newMaxResist = $this->defense->getWaterMaxResist() + $action->getPower();

        if ($newMaxResist > DefenseInterface::MAX_RESISTANCE) {
            $newMaxResist = DefenseInterface::MAX_RESISTANCE;
        }

        $this->defense->setWaterMaxResist($newMaxResist);
        $action->setRevertValue($newMaxResist - $oldMaxResist);
    }

    /**
     * Откатывает изменения максимального сопротивления урону водой
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addWaterMaxResistRevert(ActionInterface $action): void
    {
        $this->defense->setWaterMaxResist($this->defense->getWaterMaxResist() - $action->getRevertValue());
    }

    /**
     * Изменяет максимальное сопротивление урону воздухом
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addAirMaxResist(ActionInterface $action): void
    {
        $oldMaxResist = $this->defense->getAirMaxResist();
        $newMaxResist = $this->defense->getAirMaxResist() + $action->getPower();

        if ($newMaxResist > DefenseInterface::MAX_RESISTANCE) {
            $newMaxResist = DefenseInterface::MAX_RESISTANCE;
        }

        $this->defense->setAirMaxResist($newMaxResist);
        $action->setRevertValue($newMaxResist - $oldMaxResist);
    }

    /**
     * Откатывает изменения максимального сопротивления урону воздухом
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addAirMaxResistRevert(ActionInterface $action): void
    {
        $this->defense->setAirMaxResist($this->defense->getAirMaxResist() - $action->getRevertValue());
    }

    /**
     * Изменяет максимальное сопротивление урону землей
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addEarthMaxResist(ActionInterface $action): void
    {
        $oldMaxResist = $this->defense->getEarthMaxResist();
        $newMaxResist = $this->defense->getEarthMaxResist() + $action->getPower();

        if ($newMaxResist > DefenseInterface::MAX_RESISTANCE) {
            $newMaxResist = DefenseInterface::MAX_RESISTANCE;
        }

        $this->defense->setEarthMaxResist($newMaxResist);
        $action->setRevertValue($newMaxResist - $oldMaxResist);
    }

    /**
     * Откатывает изменения максимального сопротивления урону землей
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addEarthMaxResistRevert(ActionInterface $action): void
    {
        $this->defense->setEarthMaxResist($this->defense->getEarthMaxResist() - $action->getRevertValue());
    }

    /**
     * Изменяет максимальное сопротивление урону магией жизни
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addLifeMaxResist(ActionInterface $action): void
    {
        $oldMaxResist = $this->defense->getLifeMaxResist();
        $newMaxResist = $this->defense->getLifeMaxResist() + $action->getPower();

        if ($newMaxResist > DefenseInterface::MAX_RESISTANCE) {
            $newMaxResist = DefenseInterface::MAX_RESISTANCE;
        }

        $this->defense->setLifeMaxResist($newMaxResist);
        $action->setRevertValue($newMaxResist - $oldMaxResist);
    }

    /**
     * Откатывает изменения максимального сопротивления урону магией жизни
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addLifeMaxResistRevert(ActionInterface $action): void
    {
        $this->defense->setLifeMaxResist($this->defense->getLifeMaxResist() - $action->getRevertValue());
    }

    /**
     * Изменяет максимальное сопротивление урону магией смерти
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addDeathMaxResist(ActionInterface $action): void
    {
        $oldMaxResist = $this->defense->getDeathMaxResist();
        $newMaxResist = $this->defense->getDeathMaxResist() + $action->getPower();

        if ($newMaxResist > DefenseInterface::MAX_RESISTANCE) {
            $newMaxResist = DefenseInterface::MAX_RESISTANCE;
        }

        $this->defense->setDeathMaxResist($newMaxResist);
        $action->setRevertValue($newMaxResist - $oldMaxResist);
    }

    /**
     * Откатывает изменения максимального сопротивления урону магией смерти
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addDeathMaxResistRevert(ActionInterface $action): void
    {
        $this->defense->setDeathMaxResist($this->defense->getDeathMaxResist() - $action->getRevertValue());
    }

    /**
     * Увеличивает скорость создания заклинаний
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierCastSpeed(ActionInterface $action): void
    {
        if ($action->getPower() <= ActionInterface::MIN_MULTIPLIER) {
            throw new UnitException(UnitException::OVER_REDUCED . ActionInterface::MIN_MULTIPLIER);
        }

        $multiplier = $action->getPower() / 100;

        $oldCastSpeed = $this->offense->getCastSpeed();
        $newCastSpeed = $oldCastSpeed * $multiplier;

        $this->offense->setCastSpeed($newCastSpeed);
        $action->setRevertValue($newCastSpeed - $oldCastSpeed);
    }

    /**
     * Откатывает обратно увеличенную скорость атаки
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierCastSpeedRevert(ActionInterface $action): void
    {
        $this->offense->setCastSpeed($this->offense->getCastSpeed() - $action->getRevertValue());
    }

    /**
     * Изменяет игнорирование блока на фиксированную величину
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addBlockIgnore(ActionInterface $action): void
    {
        $oldBlockIgnore = $this->offense->getBlockIgnoring();
        $newBlockIgnore = $oldBlockIgnore + $action->getPower();

        if ($newBlockIgnore > DefenseInterface::MAX_BLOCK_IGNORE) {
            $newBlockIgnore = DefenseInterface::MAX_BLOCK_IGNORE;
        }

        if ($newBlockIgnore < DefenseInterface::MIN_BLOCK_IGNORE) {
            $newBlockIgnore = DefenseInterface::MIN_BLOCK_IGNORE;
        }

        $this->offense->setBlockIgnoring($newBlockIgnore);
        $action->setRevertValue($newBlockIgnore - $oldBlockIgnore);
    }

    /**
     * Откатывает изменение игнорирование блока
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addBlockIgnoreRevert(ActionInterface $action): void
    {
        $this->offense->setBlockIgnoring($this->offense->getBlockIgnoring() - $action->getRevertValue());
    }

    /**
     * Изменяет показатель вампиризма
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addVampirism(ActionInterface $action): void
    {
        $oldVampirism = $this->offense->getVampirism();
        $newVampirism = $oldVampirism + $action->getPower();

        if ($newVampirism > OffenseInterface::MAX_VAMPIRISM) {
            $newVampirism = OffenseInterface::MAX_VAMPIRISM;
        }

        if ($newVampirism < OffenseInterface::MIN_VAMPIRISM) {
            $newVampirism = OffenseInterface::MIN_VAMPIRISM;
        }

        $this->offense->setVampirism($newVampirism);
        $action->setRevertValue($newVampirism - $oldVampirism);
    }

    /**
     * Откатывает изменение показателя вампиризма
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addVampirismRevert(ActionInterface $action): void
    {
        $this->offense->setVampirism($this->offense->getVampirism() - $action->getRevertValue());
    }

    /**
     * Изменяет показатель магического вампиризма
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addMagicVampirism(ActionInterface $action): void
    {
        $oldMagicVampirism = $this->offense->getMagicVampirism();
        $newMagicVampirism = $oldMagicVampirism + $action->getPower();

        if ($newMagicVampirism > OffenseInterface::MAX_VAMPIRISM) {
            $newMagicVampirism = OffenseInterface::MAX_VAMPIRISM;
        }

        if ($newMagicVampirism < OffenseInterface::MIN_VAMPIRISM) {
            $newMagicVampirism = OffenseInterface::MIN_VAMPIRISM;
        }

        $this->offense->setMagicVampirism($newMagicVampirism);
        $action->setRevertValue($newMagicVampirism - $oldMagicVampirism);
    }

    /**
     * Откатывает изменение показателя магического вампиризма
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addMagicVampirismRevert(ActionInterface $action): void
    {
        $this->offense->setMagicVampirism($this->offense->getMagicVampirism() - $action->getRevertValue());
    }

    /**
     * Изменяет показатель общего сопротивления урону
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addGlobalResist(ActionInterface $action): void
    {
        $oldDamageResist = $this->defense->getGlobalResist();
        $newDamageResist = $oldDamageResist + $action->getPower();

        if ($newDamageResist > DefenseInterface::MAX_RESISTANCE) {
            $newDamageResist = DefenseInterface::MAX_RESISTANCE;
        }

        if ($newDamageResist < DefenseInterface::MIN_RESISTANCE) {
            $newDamageResist = DefenseInterface::MIN_RESISTANCE;
        }

        $this->defense->setGlobalResist($newDamageResist);
        $action->setRevertValue($newDamageResist - $oldDamageResist);
    }

    /**
     * Откатывает изменение показателя магического вампиризма
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addGlobalResistRevert(ActionInterface $action): void
    {
        $this->defense->setGlobalResist($this->defense->getGlobalResist() - $action->getRevertValue());
    }

    /**
     * Изменяет показатель ментального барьера
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addMentalBarrier(ActionInterface $action): void
    {
        $oldMentalBarrier = $this->defense->getMentalBarrier();
        $newMentalBarrier = $oldMentalBarrier + $action->getPower();

        if ($newMentalBarrier > DefenseInterface::MAX_MENTAL_BARRIER) {
            $newMentalBarrier = DefenseInterface::MAX_MENTAL_BARRIER;
        }

        if ($newMentalBarrier < DefenseInterface::MIN_MENTAL_BARRIER) {
            $newMentalBarrier = DefenseInterface::MIN_MENTAL_BARRIER;
        }

        $this->defense->setMentalBarrier($newMentalBarrier);
        $action->setRevertValue($newMentalBarrier - $oldMentalBarrier);
    }

    /**
     * Откатывает изменение показателя ментального барьера
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addMentalBarrierRevert(ActionInterface $action): void
    {
        $this->defense->setMentalBarrier($this->defense->getMentalBarrier() - $action->getRevertValue());
    }

    /**
     * Изменяет показатель добавляемой концентрации
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addMultiplierConcentration(ActionInterface $action): void
    {
        $oldMultiplierConcentration = $this->getAddConcentrationMultiplier();
        $newMultiplierConcentration = $oldMultiplierConcentration + $action->getPower();

        if ($newMultiplierConcentration > UnitInterface::MAX_RESOURCE_MULTIPLIER) {
            $newMultiplierConcentration = UnitInterface::MAX_RESOURCE_MULTIPLIER;
        }

        if ($newMultiplierConcentration < UnitInterface::MIN_RESOURCE_MULTIPLIER) {
            $newMultiplierConcentration = UnitInterface::MIN_RESOURCE_MULTIPLIER;
        }

        $this->setAddConcentrationMultiplier($newMultiplierConcentration);
        $action->setRevertValue($newMultiplierConcentration - $oldMultiplierConcentration);
    }

    /**
     * Откатывает изменение показателя добавляемой концентрации
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addMultiplierConcentrationRevert(ActionInterface $action): void
    {
        $this->setAddConcentrationMultiplier($this->getAddConcentrationMultiplier() - $action->getRevertValue());
    }

    /**
     * Изменяет множитель хитрости
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addMultiplierCunning(ActionInterface $action): void
    {
        $oldCunningMultiplier = $this->getCunningMultiplier();
        $newCunningMultiplier = $oldCunningMultiplier + $action->getPower();

        if ($newCunningMultiplier > UnitInterface::MAX_RESOURCE_MULTIPLIER) {
            $newCunningMultiplier = UnitInterface::MAX_RESOURCE_MULTIPLIER;
        }

        if ($newCunningMultiplier < UnitInterface::MIN_RESOURCE_MULTIPLIER) {
            $newCunningMultiplier = UnitInterface::MIN_RESOURCE_MULTIPLIER;
        }

        $this->setCunningMultiplier($newCunningMultiplier);
        $action->setRevertValue($newCunningMultiplier - $oldCunningMultiplier);
    }

    /**
     * Откатывает изменение множителя хитрости
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addMultiplierCunningRevert(ActionInterface $action): void
    {
        $this->setCunningMultiplier($this->getCunningMultiplier() - $action->getRevertValue());
    }

    /**
     * Изменяет показатель добавляемой ярости
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addMultiplierRage(ActionInterface $action): void
    {
        $oldMultiplierRage = $this->getAddRageMultiplier();
        $newMultiplierRage = $oldMultiplierRage + $action->getPower();

        if ($newMultiplierRage > UnitInterface::MAX_RESOURCE_MULTIPLIER) {
            $newMultiplierRage = UnitInterface::MAX_RESOURCE_MULTIPLIER;
        }

        if ($newMultiplierRage < UnitInterface::MIN_RESOURCE_MULTIPLIER) {
            $newMultiplierRage = UnitInterface::MIN_RESOURCE_MULTIPLIER;
        }

        $this->setAddRageMultiplier($newMultiplierRage);
        $action->setRevertValue($newMultiplierRage - $oldMultiplierRage);
    }

    /**
     * Откатывает изменение показателя добавляемой ярости
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function addMultiplierRageRevert(ActionInterface $action): void
    {
        $this->setAddRageMultiplier($this->getAddRageMultiplier() - $action->getRevertValue());
    }

    /**
     * @param ActionInterface $action
     * @return float
     * @throws Exception
     */
    private function getMultiplier(ActionInterface $action): float
    {
        if ($action->getPower() <= ActionInterface::MEW_MIN_MULTIPLIER) {
            throw new UnitException(UnitException::OVER_REDUCED . ActionInterface::MEW_MIN_MULTIPLIER);
        }

        return ($action->getPower() + 100) / 100;
    }
}
