<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\DataProvider;

use Battle\Unit\Ability\AbilityException;
use Battle\Unit\Ability\AbilityFactory;
use Battle\Unit\Ability\DataProvider\ExampleAbilityDataProvider;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class ExampleAbilityDataProviderTest extends AbstractUnitTest
{
    /**
     * Тест на успешное получение данных через ExampleAbilityDataProvider и создание из него способности
     *
     * @throws Exception
     */
    public function testExampleAbilityDataProviderCreateSuccess(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $abilityName = 'Heavy Strike';
        $abilityLevel = 1;

        $abilityData = $this->getDataProvider()->get($abilityName, $abilityLevel);

        $ability = $this->getFactory()->create($unit, $abilityData);

        self::assertEquals($abilityName, $ability->getName());
        self::assertEquals($abilityData['icon'], $ability->getIcon());
        self::assertEquals($abilityData['disposable'], $ability->isDisposable());

        // Более подробно все способности проверяются в тестах конкретных способностей Unit/Ability
    }

    /**
     * Тест на ситуацию, когда указана неизвестная способность
     *
     * @throws AbilityException
     */
    public function testExampleAbilityDataProviderUndefinedAbility(): void
    {
        $abilityName = 'Undefined ability';

        $this->expectException(AbilityException::class);
        $this->expectExceptionMessage(AbilityException::UNDEFINED_ABILITY_NAME . ': ' . $abilityName);
        $this->getDataProvider()->get($abilityName, 1);
    }

    /**
     * Тест на ситуацию, когда указан неизвестный уровень способности
     *
     * @throws AbilityException
     */
    public function testExampleAbilityDataProviderUndefinedLevel(): void
    {
        $abilityLevel = 99;

        $this->expectException(AbilityException::class);
        $this->expectExceptionMessage(AbilityException::UNDEFINED_ABILITY_LEVEL . ': ' . $abilityLevel);
        $this->getDataProvider()->get('Heavy Strike', $abilityLevel);
    }

    /**
     * @return ExampleAbilityDataProvider
     */
    private function getDataProvider(): ExampleAbilityDataProvider
    {
        return new ExampleAbilityDataProvider();
    }

    /**
     * @return AbilityFactory
     */
    private function getFactory(): AbilityFactory
    {
        return new AbilityFactory();
    }
}
