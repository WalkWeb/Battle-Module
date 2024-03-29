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

class ProtectiveStandAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/266.png" alt="" /> <span class="ability">Protective Stand</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/266.png" alt="" /> <span class="ability">Защитная стойка</span>';

    /**
     * Тест на создание способности Protective Stand через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testProtectiveStandAbilityCreate(): void
    {
        $this->assertCreateEffectAbility(
            4,
            'Protective Stand',
            '/images/icons/ability/266.png',
            AbilityInterface::ACTIVATE_RAGE
        );
    }

    /**
     * Тест на применение способности Protective Stand
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedPhysicalResist
     * @param int $expectedFireResist
     * @param int $expectedWaterResist
     * @param int $expectedAirResist
     * @param int $expectedEarthResist
     * @param int $expectedLifeResist
     * @param int $expectedDeathResist
     * @param int $expectedPhysicalMaxResist
     * @param int $expectedFireMaxResist
     * @param int $expectedEffectDuration
     * @throws Exception
     */
    public function testProtectiveStandAbilityUse(
        int $level,
        int $expectedPhysicalResist,
        int $expectedFireResist,
        int $expectedWaterResist,
        int $expectedAirResist,
        int $expectedEarthResist,
        int $expectedLifeResist,
        int $expectedDeathResist,
        int $expectedPhysicalMaxResist,
        int $expectedFireMaxResist,
        int $expectedEffectDuration
    ): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $statistics = new Statistic();

        $ability = $this->getAbility($unit, 'Protective Stand', $level);

        $this->activateAbility($ability, $unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(1, $actions);

        foreach ($actions as $i => $action) {
            $scenario = new Scenario();

            self::assertInstanceOf(EffectAction::class, $action);
            self::assertTrue($action->canByUsed());
            $action->handle();

            // Сообщение об эффекте
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

        self::assertEquals($expectedPhysicalResist, $unit->getDefense()->getPhysicalResist());
        self::assertEquals($expectedFireResist, $unit->getDefense()->getFireResist());
        self::assertEquals($expectedWaterResist, $unit->getDefense()->getWaterResist());
        self::assertEquals($expectedAirResist, $unit->getDefense()->getAirResist());
        self::assertEquals($expectedEarthResist, $unit->getDefense()->getEarthResist());
        self::assertEquals($expectedLifeResist, $unit->getDefense()->getLifeResist());
        self::assertEquals($expectedDeathResist, $unit->getDefense()->getDeathResist());

        self::assertEquals($expectedPhysicalMaxResist, $unit->getDefense()->getPhysicalMaxResist());
        self::assertEquals($expectedFireMaxResist, $unit->getDefense()->getFireMaxResist());

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
                20,
                20,
                10,
                10,
                10,
                10,
                10,
                79,
                79,
                5,
            ],
            [
                2,
                22,
                22,
                11,
                11,
                11,
                11,
                11,
                80,
                80,
                6,
            ],
            [
                3,
                24,
                24,
                12,
                12,
                12,
                12,
                12,
                81,
                81,
                7,
            ],
            [
                4,
                26,
                26,
                13,
                13,
                13,
                13,
                13,
                82,
                82,
                8,
            ],
            [
                5,
                28,
                28,
                14,
                14,
                14,
                14,
                14,
                83,
                83,
                9,
            ],
        ];
    }
}
