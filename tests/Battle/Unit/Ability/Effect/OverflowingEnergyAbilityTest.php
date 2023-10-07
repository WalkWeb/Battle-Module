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

class OverflowingEnergyAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/403.png" alt="" /> <span class="ability">Overflowing Energy</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/403.png" alt="" /> <span class="ability">Переполняющая энергия</span>';

    /**
     * Тест на создание способности Overflowing Energy через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testOverflowingEnergyAbilityCreate(): void
    {
        $this->assertCreateEffectAbility(
            4,
            'Overflowing Energy',
            '/images/icons/ability/403.png',
            AbilityInterface::ACTIVATE_CUNNING
        );
    }

    /**
     * Тест на применение способности Overflowing Energy
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedCriticalChance
     * @param int $expectedCriticalMultiplier
     * @param int $expectedEffectDuration
     * @throws Exception
     */
    public function testOverflowingEnergyAbilityUse(
        int $level,
        int $expectedCriticalChance,
        int $expectedCriticalMultiplier,
        int $expectedEffectDuration
    ): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $statistics = new Statistic();

        $ability = $this->createAbilityByDataProvider($unit, 'Overflowing Energy', $level);

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
        self::assertEquals($expectedCriticalChance, $unit->getOffense()->getCriticalChance());
        self::assertEquals($expectedCriticalMultiplier, $unit->getOffense()->getCriticalMultiplier());

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
                6,
                220,
                5,
            ],
            [
                2,
                6,
                229,
                5,
            ],
            [
                3,
                7,
                240,
                6,
            ],
            [
                4,
                7,
                250,
                6,
            ],
            [
                5,
                8,
                260,
                7,
            ],
        ];
    }
}
