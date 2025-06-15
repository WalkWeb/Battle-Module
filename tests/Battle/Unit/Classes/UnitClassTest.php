<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes;

use Battle\Command\CommandFactory;
use Battle\Container\ContainerInterface;
use Battle\Unit\Classes\DataProvider\ClassDataProviderInterface;
use Battle\Unit\Classes\DataProvider\ExampleClassDataProvider;
use Battle\Unit\Classes\UnitClass;
use Battle\Unit\Classes\UnitClassException;
use Exception;
use Tests\AbstractTestCase;
use Tests\Factory\UnitFactory;

class UnitClassTest extends AbstractTestCase
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
            $this->convertAbilityData($classData['abilities'], $this->container),
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

        foreach ($unit->getClass()->getAbilities($unit) as $ability) {
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
        return new ExampleClassDataProvider();
    }

    /**
     * @param array $abilitiesData
     * @param ContainerInterface $container
     * @return array
     */
    private function convertAbilityData(array $abilitiesData, ContainerInterface $container): array
    {
        return array_map(function ($abilityData) use ($container) {
            return $container->getAbilityDataProvider()->get($abilityData['name'], $abilityData['level']);
        }, $abilitiesData);
    }
}
