<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Defense;

use Battle\Unit\Defense\Defense;
use Battle\Unit\Defense\DefenseException;
use Battle\Unit\Defense\DefenseInterface;
use Exception;
use Tests\AbstractUnitTest;

class DefenseTest extends AbstractUnitTest
{
    /**
     * Тест на создание Defense
     *
     * @throws Exception
     */
    public function testDefenseCreate(): void
    {
        $defenseValue = 100;
        $magicDefence = 50;
        $block = 0;
        $mentalBarrier = 50;

        $defense = new Defense($defenseValue, $magicDefence, $block, $mentalBarrier);

        self::assertEquals($defenseValue, $defense->getDefense());
        self::assertEquals($magicDefence, $defense->getMagicDefense());
        self::assertEquals($block, $defense->getBlock());
        self::assertEquals($mentalBarrier, $defense->getMentalBarrier());
    }

    /**
     * Тест на обновление параметров Defense
     *
     * @throws DefenseException
     */
    public function testDefenseUpdate(): void
    {
        $defense = new Defense(10, 10, 10, 0);

        $defense->setDefense($defenseValue = 1000);
        $defense->setMagicDefense($magicDefence = 500);
        $defense->setBlock($block = 5);
        $defense->setMentalBarrier($mentalBarrier = 50);

        self::assertEquals($defenseValue, $defense->getDefense());
        self::assertEquals($magicDefence, $defense->getMagicDefense());
        self::assertEquals($block, $defense->getBlock());
        self::assertEquals($mentalBarrier, $defense->getMentalBarrier());
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение защиты
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinDefense(): void
    {
        $defense = new Defense(10, 10, 10, 0);

        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_DEFENSE_VALUE . DefenseInterface::MIN_DEFENSE . '-' . DefenseInterface::MAX_DEFENSE
        );
        $defense->setDefense(DefenseInterface::MIN_DEFENSE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение защиты
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxDefense(): void
    {
        $defense = new Defense(10, 10, 10, 0);

        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_DEFENSE_VALUE . DefenseInterface::MIN_DEFENSE . '-' . DefenseInterface::MAX_DEFENSE
        );
        $defense->setDefense(DefenseInterface::MAX_DEFENSE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение магической защиты
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinMagicDefense(): void
    {
        $defense = new Defense(10, 10, 10, 0);

        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAGIC_DEFENSE_VALUE . DefenseInterface::MIN_MAGIC_DEFENSE . '-' . DefenseInterface::MAX_MAGIC_DEFENSE
        );
        $defense->setMagicDefense(DefenseInterface::MIN_MAGIC_DEFENSE - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение магической защиты
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxMagicDefense(): void
    {
        $defense = new Defense(10, 10, 10, 0);

        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MAGIC_DEFENSE_VALUE . DefenseInterface::MIN_MAGIC_DEFENSE . '-' . DefenseInterface::MAX_MAGIC_DEFENSE
        );
        $defense->setMagicDefense(DefenseInterface::MAX_MAGIC_DEFENSE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение блока
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinBlock(): void
    {
        $defense = new Defense(10, 10, 10, 0);

        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_BLOCK_VALUE . DefenseInterface::MIN_BLOCK . '-' . DefenseInterface::MAX_BLOCK
        );
        $defense->setBlock(DefenseInterface::MIN_BLOCK - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение блока
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxBlock(): void
    {
        $defense = new Defense(10, 10, 10, 0);

        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_BLOCK_VALUE . DefenseInterface::MIN_BLOCK . '-' . DefenseInterface::MAX_BLOCK
        );
        $defense->setBlock(DefenseInterface::MAX_BLOCK + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение ментального барьера
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinMentalBarrier(): void
    {
        $defense = new Defense(10, 10, 10, 0);

        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MENTAL_BARRIER_VALUE . DefenseInterface::MIN_MENTAL_BARRIER . '-' . DefenseInterface::MAX_MENTAL_BARRIER
        );
        $defense->setMentalBarrier(DefenseInterface::MIN_MENTAL_BARRIER - 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком большое значение ментального барьера
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMaxMentalBarrier(): void
    {
        $defense = new Defense(10, 10, 10, 0);

        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_MENTAL_BARRIER_VALUE . DefenseInterface::MIN_MENTAL_BARRIER . '-' . DefenseInterface::MAX_MENTAL_BARRIER
        );
        $defense->setMentalBarrier(DefenseInterface::MAX_MENTAL_BARRIER + 1);
    }
}
