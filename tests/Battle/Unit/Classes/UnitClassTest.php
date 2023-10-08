<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes;

use Battle\Command\CommandFactory;
use Battle\Unit\Classes\DataProvider\ClassDataProviderInterface;
use Battle\Unit\Classes\DataProvider\ExampleClassDataProvider;
use Battle\Unit\Classes\UnitClass;
use Battle\Unit\Classes\UnitClassException;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class UnitClassTest extends AbstractUnitTest
{
    /**
     * Тест на успешное создание класса юнита через универсальный класс UnitClass
     *
     * @throws Exception
     */
    public function testUnitClassCreate(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $classData = $this->getClassDataProvider()->get(1);

        $class = new UnitClass(
            $classData['id'],
            $classData['name'],
            $classData['small_icon'],
            $classData['abilities'],
            $this->container
        );

        // Проверяем базовые параметры
        self::assertEquals($classData['id'], $class->getId());
        self::assertEquals($classData['name'], $class->getName());
        self::assertEquals($classData['small_icon'], $class->getSmallIcon());

        // Проверяем, что actions-способностей созданные через массив параметров соответствуют аналогам из класса Warrior
        self::assertSameSize(
            $unit->getClass()->getAbilities($unit),
            $class->getAbilities($unit)
        );

        $expectedAbilities = [];

        foreach ($unit->getClass()->getAbilities($unit) as $i => $ability) {
            $expectedAbilities[] = $ability;
        }

        foreach ($class->getAbilities($unit) as $i => $ability) {
            self::assertEquals(
                $expectedAbilities[$i]->getActions($enemyCommand, $command),
                $ability->getActions($enemyCommand, $command)
            );
        }
    }

    /**
     * Тест на ситуацию, когда переданный массив $abilitiesData не содержит внутри себя массивы
     *
     * @throws Exception
     */
    public function testUnitClassInvalidAbilitiesData(): void
    {
        $this->expectException(UnitClassException::class);
        $this->expectExceptionMessage(UnitClassException::INVALID_ABILITY_DATA);

        new UnitClass(
            15,
            'Demo Class',
            'icon.png',
            ['invalid_data'],
            $this->container
        );
    }

    /**
     * @return ClassDataProviderInterface
     */
    private function getClassDataProvider(): ClassDataProviderInterface
    {
        return new ExampleClassDataProvider($this->container);
    }
}
