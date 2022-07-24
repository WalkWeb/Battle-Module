<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Offense;

use Battle\Unit\Offense\Offense;
use Battle\Unit\Offense\OffenseException;
use Battle\Unit\Offense\OffenseInterface;
use Tests\AbstractUnitTest;

class OffenseTest extends AbstractUnitTest
{
    /**
     * Тест на создание Offense
     *
     * @throws OffenseException
     */
    public function testOffenseCreate(): void
    {
        $typeDamage = 1;
        $damage = 100;
        $attackSpeed = 1.2;
        $accuracy = 200;
        $magicAccuracy = 100;
        $blockIgnore = 0;

        $offense = new Offense($typeDamage, $damage, $attackSpeed, $accuracy, $magicAccuracy, $blockIgnore);

        self::assertEquals($typeDamage, $offense->getTypeDamage());
        self::assertEquals($damage, $offense->getDamage());
        self::assertEquals($attackSpeed, $offense->getAttackSpeed());
        self::assertEquals($accuracy, $offense->getAccuracy());
        self::assertEquals($magicAccuracy, $offense->getMagicAccuracy());
        self::assertEquals($blockIgnore, $offense->getBlockIgnore());
        self::assertEquals(round($damage * $attackSpeed, 1), $offense->getDPS());
    }

    /**
     * Тест на обновление Offense
     * 
     * @throws OffenseException
     */
    public function testOffenseUpdate(): void
    {
        $offense = new Offense(1, 10, 1, 100, 50, 0);

        $offense->setDamage($damage = 50);
        $offense->setAttackSpeed($attackSpeed = 1.2);
        $offense->setAccuracy($accuracy = 250);
        $offense->setMagicAccuracy($magicAccuracy = 150);
        $offense->setBlockIgnore($blockIgnore = 100);

        self::assertEquals($damage, $offense->getDamage());
        self::assertEquals($attackSpeed, $offense->getAttackSpeed());
        self::assertEquals($accuracy, $offense->getAccuracy());
        self::assertEquals($magicAccuracy, $offense->getMagicAccuracy());
        self::assertEquals($blockIgnore, $offense->getBlockIgnore());
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение урона
     *
     * @throws OffenseException
     */
    public function testOffenseSetUltraMinDamage(): void
    {
        $offense = new Offense(1, 10, 1, 100, 50, 0);

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );
        $offense->setDamage(OffenseInterface::MIN_DAMAGE - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение урона
     *
     * @throws OffenseException
     */
    public function testOffenseSetUltraMaxDamage(): void
    {
        $offense = new Offense(1, 10, 1, 100, 50, 0);

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );
        $offense->setDamage(OffenseInterface::MAX_DAMAGE + 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение скорости атаки
     *
     * @throws OffenseException
     */
    public function testOffenseSetUltraMinAttackSpeed(): void
    {
        $offense = new Offense(1, 10, 1, 100, 50, 0);

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_ATTACK_SPEED_VALUE . OffenseInterface::MIN_ATTACK_SPEED . '-' . OffenseInterface::MAX_ATTACK_SPEED
        );
        $offense->setAttackSpeed(OffenseInterface::MIN_ATTACK_SPEED - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение скорости атаки
     *
     * @throws OffenseException
     */
    public function testOffenseSetUltraMaxAttackSpeed(): void
    {
        $offense = new Offense(1, 10, 1, 100, 50, 0);

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_ATTACK_SPEED_VALUE . OffenseInterface::MIN_ATTACK_SPEED . '-' . OffenseInterface::MAX_ATTACK_SPEED
        );
        $offense->setAttackSpeed(OffenseInterface::MAX_ATTACK_SPEED + 0.01);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение меткости
     *
     * @throws OffenseException
     */
    public function testOffenseSetUltraMinAccuracy(): void
    {
        $offense = new Offense(1, 10, 1, 100, 50, 0);

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_ACCURACY_VALUE . OffenseInterface::MIN_ACCURACY . '-' . OffenseInterface::MAX_ACCURACY
        );
        $offense->setAccuracy(OffenseInterface::MIN_ACCURACY - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение меткости
     *
     * @throws OffenseException
     */
    public function testOffenseSetUltraMaxAccuracy(): void
    {
        $offense = new Offense(1, 10, 1, 100, 50, 0);

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_ACCURACY_VALUE . OffenseInterface::MIN_ACCURACY . '-' . OffenseInterface::MAX_ACCURACY
        );
        $offense->setAccuracy(OffenseInterface::MAX_ACCURACY + 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение магической меткости
     *
     * @throws OffenseException
     */
    public function testOffenseSetUltraMinMagicAccuracy(): void
    {
        $offense = new Offense(1, 10, 1, 100, 50, 0);

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_MAGIC_ACCURACY_VALUE . OffenseInterface::MIN_MAGIC_ACCURACY . '-' . OffenseInterface::MAX_MAGIC_ACCURACY
        );
        $offense->setMagicAccuracy(OffenseInterface::MIN_MAGIC_ACCURACY - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение магической меткости
     *
     * @throws OffenseException
     */
    public function testOffenseSetUltraMaxMagicAccuracy(): void
    {
        $offense = new Offense(1, 10, 1, 100, 50, 0);

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_MAGIC_ACCURACY_VALUE . OffenseInterface::MIN_MAGIC_ACCURACY . '-' . OffenseInterface::MAX_MAGIC_ACCURACY
        );
        $offense->setMagicAccuracy(OffenseInterface::MAX_MAGIC_ACCURACY + 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение игнорирования блока
     *
     * @throws OffenseException
     */
    public function testOffenseSetUltraMinBlockIgnore(): void
    {
        $offense = new Offense(1, 10, 1, 100, 50, 0);

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_BLOCK_IGNORE_VALUE . OffenseInterface::MIN_BLOCK_IGNORE . '-' . OffenseInterface::MAX_BLOCK_IGNORE
        );
        $offense->setBlockIgnore(OffenseInterface::MIN_BLOCK_IGNORE - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение игнорирования блока
     *
     * @throws OffenseException
     */
    public function testOffenseSetUltraMaxBlockIgnore(): void
    {
        $offense = new Offense(1, 10, 1, 100, 50, 0);

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_BLOCK_IGNORE_VALUE . OffenseInterface::MIN_BLOCK_IGNORE . '-' . OffenseInterface::MAX_BLOCK_IGNORE
        );
        $offense->setBlockIgnore(OffenseInterface::MAX_BLOCK_IGNORE + 1);
    }

    /**
     * Тест на ситуацию, когда передан некорректный тип урона
     *
     * @throws OffenseException
     */
    public function testOffenseInvalidTypeDamage(): void
    {
        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(OffenseException::INCORRECT_TYPE_DAMAGE_VALUE);
        new Offense(3, 10, 1, 100, 50, 0);
    }
}
