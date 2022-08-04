<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Offense;

use Battle\Unit\Defense\Defense;
use Battle\Unit\Defense\DefenseException;
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
     * @throws DefenseException
     */
    public function testOffenseCreate(): void
    {
        $typeDamage = 1;
        $physicalDamage = 300;
        $attackSpeed = 1.2;
        $accuracy = 200;
        $magicAccuracy = 100;
        $blockIgnore = 0;

        $offense = new Offense($typeDamage, $physicalDamage, $attackSpeed, $accuracy, $magicAccuracy, $blockIgnore);
        $defense = new Defense(0, 10, 10, 10, 5, 0);

        self::assertEquals($typeDamage, $offense->getTypeDamage());
        self::assertEquals($physicalDamage, $offense->getDamage($defense));
        self::assertEquals($physicalDamage, $offense->getPhysicalDamage());
        self::assertEquals($attackSpeed, $offense->getAttackSpeed());
        self::assertEquals($accuracy, $offense->getAccuracy());
        self::assertEquals($magicAccuracy, $offense->getMagicAccuracy());
        self::assertEquals($blockIgnore, $offense->getBlockIgnore());
        self::assertEquals(round($physicalDamage * $attackSpeed, 1), $offense->getDPS());
    }

    /**
     * Тест на обновление Offense
     *
     * @throws OffenseException
     * @throws DefenseException
     */
    public function testOffenseUpdate(): void
    {
        $offense = new Offense(1, 20, 1, 100, 50, 0);
        $defense = new Defense(0, 10, 10, 10, 5, 0);

        $offense->setPhysicalDamage($physicalDamage = 15);
        $offense->setAttackSpeed($attackSpeed = 1.2);
        $offense->setAccuracy($accuracy = 250);
        $offense->setMagicAccuracy($magicAccuracy = 150);
        $offense->setBlockIgnore($blockIgnore = 100);

        self::assertEquals($physicalDamage, $offense->getDamage($defense));
        self::assertEquals($physicalDamage, $offense->getPhysicalDamage());
        self::assertEquals($attackSpeed, $offense->getAttackSpeed());
        self::assertEquals($accuracy, $offense->getAccuracy());
        self::assertEquals($magicAccuracy, $offense->getMagicAccuracy());
        self::assertEquals($blockIgnore, $offense->getBlockIgnore());
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком низкое значение физического урона
     *
     * @throws OffenseException
     */
    public function testOffenseSetUltraMinPhysicalDamage(): void
    {
        $offense = new Offense(1, 10, 1, 100, 50, 0);

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_PHYSICAL_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );
        $offense->setPhysicalDamage(OffenseInterface::MIN_DAMAGE - 1);
    }

    /**
     * Тест на ошибку, когда в Offense пытаются записать слишком высокое значение физического урона
     *
     * @throws OffenseException
     */
    public function testOffenseSetUltraMaxPhysicalDamage(): void
    {
        $offense = new Offense(1, 10, 1, 100, 50, 0);

        $this->expectException(OffenseException::class);
        $this->expectExceptionMessage(
            OffenseException::INCORRECT_PHYSICAL_DAMAGE_VALUE . OffenseInterface::MIN_DAMAGE . '-' . OffenseInterface::MAX_DAMAGE
        );
        $offense->setPhysicalDamage(OffenseInterface::MAX_DAMAGE + 1);
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

    /**
     * @dataProvider resistDataProvider
     * @param int $physicalDamage
     * @param int $physicalResist
     * @param int $exceptedDamage
     * @throws OffenseException
     * @throws DefenseException
     */
    public function testOffenseResist(int $physicalDamage, int $physicalResist, int $exceptedDamage): void
    {
        $offense = new Offense(1, $physicalDamage, 1, 100, 50, 0);
        $defense = new Defense($physicalResist, 10, 10, 10, 5, 0);

        self::assertEquals($exceptedDamage, $offense->getDamage($defense));
    }

    /**
     * @return array
     */
    public function resistDataProvider(): array
    {
        return [
            [
                100,
                0,
                100,
            ],
            [
                100,
                25,
                75,
            ],
            [
                100,
                50,
                50,
            ],
            [
                100,
                75,
                25,
            ],
            [
                100,
                100,
                0,
            ],
            [
                100,
                -100,
                200,
            ],
        ];
    }
}
