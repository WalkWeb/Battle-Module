<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Defense;

use Battle\Unit\Defense\Defense;
use Battle\Unit\Defense\DefenseException;
use Battle\Unit\Defense\DefenseInterface;
use Tests\AbstractUnitTest;

class DefenseTest extends AbstractUnitTest
{
    /**
     * Тест на создание Defense
     */
    public function testDefenseCreate(): void
    {
        $defenseValue = 100;
        $block = 0;

        $defense = new Defense($defenseValue, $block);

        self::assertEquals($defenseValue, $defense->getDefense());
        self::assertEquals($block, $defense->getBlock());
    }

    /**
     * Тест на обновление параметров Defense
     *
     * @throws DefenseException
     */
    public function testDefenseUpdate(): void
    {
        $defense = new Defense(10, 10);

        $defense->setDefense($defenseValue = 1000);
        $defense->setBlock($block = 5);

        self::assertEquals($defenseValue, $defense->getDefense());
        self::assertEquals($block, $defense->getBlock());
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение защиты
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinDefense(): void
    {
        $defense = new Defense(10, 10);

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
        $defense = new Defense(10, 10);

        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_DEFENSE_VALUE . DefenseInterface::MIN_DEFENSE . '-' . DefenseInterface::MAX_DEFENSE
        );
        $defense->setDefense(DefenseInterface::MAX_DEFENSE + 1);
    }

    /**
     * Тест на ошибку, когда в Defense пытаются записать слишком низкое значение блока
     *
     * @throws DefenseException
     */
    public function testDefenseSetUltraMinBlock(): void
    {
        $defense = new Defense(10, 10);

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
        $defense = new Defense(10, 10);

        $this->expectException(DefenseException::class);
        $this->expectExceptionMessage(
            DefenseException::INCORRECT_BLOCK_VALUE . DefenseInterface::MIN_BLOCK . '-' . DefenseInterface::MAX_BLOCK
        );
        $defense->setBlock(DefenseInterface::MAX_BLOCK + 1);
    }
}
