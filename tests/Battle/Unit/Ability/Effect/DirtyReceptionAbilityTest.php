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

class DirtyReceptionAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/248.png" alt="" /> <span class="ability">Dirty Reception</span> on <span style="color: #1e72e3">unit_1</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/248.png" alt="" /> <span class="ability">Грязный прием</span> на <span style="color: #1e72e3">unit_1</span>';

    /**
     * Тест на создание способности Dirty Reception через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testDirtyReceptionAbilityCreate(): void
    {
        $this->assertCreateEffectAbility(
            4,
            'Dirty Reception',
            '/images/icons/ability/248.png',
            AbilityInterface::ACTIVATE_CUNNING
        );
    }

    /**
     * Тест на применение способности Dirty Reception
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param float $expectedAttackSpeed
     * @param float $expectedCastSpeed
     * @param int $expectedDefense
     * @param int $expectedEffectDuration
     * @throws Exception
     */
    public function testDirtyReceptionAbilityUse(
        int $level,
        float $expectedAttackSpeed,
        float $expectedCastSpeed,
        int $expectedDefense,
        int $expectedEffectDuration
    ): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $statistics = new Statistic();

        $ability = $this->createAbilityByDataProvider($unit, 'Dirty Reception', $level);

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
        self::assertCount(1, $enemyUnit->getEffects());

        // Проверяем длительность эффекта
        foreach ($enemyUnit->getEffects() as $effect) {
            self::assertEquals($expectedEffectDuration, $effect->getDuration());
        }

        // Проверяем обновленные параметры
        self::assertEquals($expectedAttackSpeed, $enemyUnit->getOffense()->getAttackSpeed());
        self::assertEquals($expectedCastSpeed, $enemyUnit->getOffense()->getCastSpeed());
        self::assertEquals($expectedDefense, $enemyUnit->getDefense()->getDefense());

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
                0.83,
                1.0,
                85,
                5,
            ],
            [
                2,
                0.81,
                0.97,
                82,
                5,
            ],
            [
                3,
                0.79,
                0.95,
                79,
                6,
            ],
            [
                4,
                0.77,
                0.92,
                76,
                6,
            ],
            [
                5,
                0.75,
                0.9,
                73,
                7,
            ],
        ];
    }
}
