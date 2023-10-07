<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\EffectAction;
use Battle\Command\CommandFactory;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Battle\Unit\Ability\AbilityInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class PotionOfPowerAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/085.png" alt="" /> <span class="ability">Potion of Power</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/085.png" alt="" /> <span class="ability">Зелье мощи</span>';

    /**
     * Тест на создание способности Potion of Power через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testPotionOfPowerAbilityCreate(): void
    {
        $this->assertCreateEffectAbility(
            4,
            'Potion of Power',
            '/images/icons/ability/085.png',
            AbilityInterface::ACTIVATE_RAGE
        );
    }

    /**
     * Тест на применение способности Potion of Power
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedDamage
     * @param int $expectedPhysicalResist
     * @param int $expectedFireResist
     * @param int $expectedWaterResist
     * @param int $expectedAirResist
     * @param int $expectedEarthResist
     * @param int $expectedLifeResist
     * @param int $expectedDeathResist
     * @param int $expectedEffectDuration
     * @throws Exception
     */
    public function testPotionOfPowerAbilityUse(
        int $level,
        int $expectedDamage,
        int $expectedPhysicalResist,
        int $expectedFireResist,
        int $expectedWaterResist,
        int $expectedAirResist,
        int $expectedEarthResist,
        int $expectedLifeResist,
        int $expectedDeathResist,
        int $expectedEffectDuration
    ): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $statistics = new Statistic();

        $ability = $this->createAbilityByDataProvider($unit, 'Potion of Power', $level);

        $this->activateAbility($ability, $unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(1, $actions);

        foreach ($actions as $i => $action) {
            $scenario = new Scenario();

            self::assertInstanceOf(EffectAction::class, $action);
            self::assertTrue($action->canByUsed());
            $action->handle();

            // Сообщений об эффекте
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));

            // Проверяем что создана анимация применения эффекта
            $scenario->addAnimation($action, $statistics);
            self::assertCount(1, $scenario->getArray());
        }

        // Проверяем наличие эффекта
        self::assertCount(1, $unit->getEffects());

        // Проверяем длительность эффекта
        foreach ($unit->getEffects() as $effect) {
            self::assertEquals($expectedEffectDuration, $effect->getDuration());
        }

        // Проверяем обновленные параметры
        self::assertEquals($expectedDamage, $unit->getOffense()->getDamage($enemyUnit->getDefense()));
        self::assertEquals($expectedPhysicalResist, $unit->getDefense()->getPhysicalResist());
        self::assertEquals($expectedFireResist, $unit->getDefense()->getFireResist());
        self::assertEquals($expectedWaterResist, $unit->getDefense()->getWaterResist());
        self::assertEquals($expectedAirResist, $unit->getDefense()->getAirResist());
        self::assertEquals($expectedEarthResist, $unit->getDefense()->getAirResist());
        self::assertEquals($expectedLifeResist, $unit->getDefense()->getLifeResist());
        self::assertEquals($expectedDeathResist, $unit->getDefense()->getDeathResist());

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * @return array
     */
    public function useDataProvider(): array
    {
        return [
            [
                1,
                18,
                10,
                10,
                10,
                10,
                10,
                10,
                10,
                5,
            ],
            [
                2,
                18,
                13,
                13,
                13,
                13,
                13,
                13,
                13,
                5,
            ],
            [
                3,
                18,
                16,
                16,
                16,
                16,
                16,
                16,
                16,
                6,
            ],
            [
                4,
                18,
                19,
                19,
                19,
                19,
                19,
                19,
                19,
                6,
            ],
            [
                5,
                19,
                22,
                22,
                22,
                22,
                22,
                22,
                22,
                7,
            ],
        ];
    }
}
