<?php

declare(strict_types=1);

namespace Battle\Unit\Traits;

use Battle\Action\ActionInterface;
use Battle\Unit\Defense\DefenseInterface;
use Battle\Unit\UnitException;
use Exception;

/**
 * В этом трейте хранятся однотипные (в основном) методы на изменение характеристик юнита (и откат изменений)
 *
 * Изменения всех характеристик можно сделать одним магическим (в смысле навороченным) методом, который на основании
 * названия свойства юнита будет находить его и изменять (при этом часть будет в классе Unit, часть в Offense, а часть в
 * Defense.
 *
 * Но делаются отдельные методы на каждый параметр, чтобы через покрытие авто-тестами можно было убедиться, что
 * изменения всех параметров покрыты тесты.
 *
 * @package Battle\Unit\Traits
 */
trait ModifyStatsTrait
{
    /**
     * Увеличивает урон юнита (можно сделать и уменьшение, но пока делаем только увеличение)
     *
     * @param ActionInterface $action - ожидается BuffAction
     * @throws Exception
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
     * @throws Exception
     */
    private function multiplierPhysicalDamageRevert(ActionInterface $action): void
    {
        $this->offense->setPhysicalDamage($this->offense->getPhysicalDamage() - $action->getRevertValue());
    }

    /**
     * Изменяет меткость юнита
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierAccuracy(ActionInterface $action): void
    {
        if ($action->getPower() <= ActionInterface::MIN_MULTIPLIER) {
            throw new UnitException(UnitException::OVER_REDUCED . ActionInterface::MIN_MULTIPLIER);
        }

        $multiplier = $action->getPower() / 100;

        $oldAccuracy = $this->offense->getAccuracy();
        $newAccuracy = (int)($this->offense->getAccuracy() * $multiplier);

        $bonus = $newAccuracy - $oldAccuracy;

        $this->offense->setAccuracy($newAccuracy);

        $action->setRevertValue($bonus);
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
     * Изменяет магическую меткость юнита
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierMagicAccuracy(ActionInterface $action): void
    {
        if ($action->getPower() <= ActionInterface::MIN_MULTIPLIER) {
            throw new UnitException(UnitException::OVER_REDUCED . ActionInterface::MIN_MULTIPLIER);
        }

        $multiplier = $action->getPower() / 100;

        $oldMagicAccuracy = $this->offense->getMagicAccuracy();
        $newMagicAccuracy = (int)($this->offense->getMagicAccuracy() * $multiplier);

        $bonus = $newMagicAccuracy - $oldMagicAccuracy;

        $this->offense->setMagicAccuracy($newMagicAccuracy);

        $action->setRevertValue($bonus);
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
     * Изменяет защиту юнита
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierDefense(ActionInterface $action): void
    {
        if ($action->getPower() <= ActionInterface::MIN_MULTIPLIER) {
            throw new UnitException(UnitException::OVER_REDUCED . ActionInterface::MIN_MULTIPLIER);
        }

        $multiplier = $action->getPower() / 100;

        $oldDefense = $this->defense->getDefense();
        $newDefense = (int)($this->defense->getDefense() * $multiplier);

        $bonus = $newDefense - $oldDefense;

        $this->defense->setDefense($newDefense);

        $action->setRevertValue($bonus);
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
     * Изменяет магическую защиту юнита
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierMagicDefense(ActionInterface $action): void
    {
        if ($action->getPower() <= ActionInterface::MIN_MULTIPLIER) {
            throw new UnitException(UnitException::OVER_REDUCED . ActionInterface::MIN_MULTIPLIER);
        }

        $multiplier = $action->getPower() / 100;

        $oldMagicDefense = $this->defense->getMagicDefense();
        $newMagicDefense = (int)($this->defense->getMagicDefense() * $multiplier);

        $bonus = $newMagicDefense - $oldMagicDefense;

        $this->defense->setMagicDefense($newMagicDefense);

        $action->setRevertValue($bonus);
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
     * Увеличивает здоровье юнита (можно сделать и уменьшение, но пока делаем только увеличение)
     *
     * @param ActionInterface $action - ожидается BuffAction
     * @throws Exception
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
     * Увеличивает скорость атаки юнита
     *
     * @param ActionInterface $action - ожидается BuffAction
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
     */
    private function addBlockRevert(ActionInterface $action): void
    {
        $block = $this->defense->getBlock();
        $block -= $action->getRevertValue();
        $this->defense->setBlock($block);
    }

    /**
     * Изменяет шанс критического удара юнита
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

        $bonus = $newCriticalChance - $oldCriticalChance;

        $this->offense->setCriticalChance($newCriticalChance);

        $action->setRevertValue($bonus);
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
     * Изменяет силу критического удара юнита
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

        $bonus = $newCriticalMultiplier - $oldCriticalMultiplier;

        $this->offense->setCriticalMultiplier($newCriticalMultiplier);

        $action->setRevertValue($bonus);
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
     * Изменяет урон огнем юнита
     *
     * @param ActionInterface $action
     * @throws Exception
     */
    private function multiplierFireDamage(ActionInterface $action): void
    {
        if ($action->getPower() <= ActionInterface::MIN_MULTIPLIER) {
            throw new UnitException(UnitException::OVER_REDUCED . ActionInterface::MIN_MULTIPLIER);
        }

        $multiplier = $action->getPower() / 100;

        $oldFireDamage = $this->offense->getFireDamage();
        $newFireDamage = (int)($this->offense->getFireDamage() * $multiplier);

        $bonus = $newFireDamage - $oldFireDamage;

        $this->offense->setFireDamage($newFireDamage);

        $action->setRevertValue($bonus);
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
     * Изменяет урон водой юнита
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

        $bonus = $newWaterDamage - $oldWaterDamage;

        $this->offense->setWaterDamage($newWaterDamage);

        $action->setRevertValue($bonus);
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
}
