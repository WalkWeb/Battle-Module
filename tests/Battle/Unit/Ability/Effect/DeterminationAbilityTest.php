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

class DeterminationAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/392.png" alt="" /> <span class="ability">Determination</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/392.png" alt="" /> <span class="ability">Решимость</span>';

    /**
     * Тест на создание способности Determination через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testDeterminationAbilityCreate(): void
    {
        $this->assertCreateEffectAbility(
            4,
            'Determination',
            '/images/icons/ability/392.png',
            AbilityInterface::ACTIVATE_RAGE
        );
    }

    /**
     * Тест на применение способности Determination
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedDamageMultiplier
     * @param int $expectedAccuracy
     * @param int $expectedMagicAccuracy
     * @param int $expectedDefense
     * @param int $expectedMagicDefense
     * @param int $expectedEffectDuration
     * @throws Exception
     */
    public function testDeterminationAbilityUse(
        int $level,
        int $expectedDamageMultiplier,
        int $expectedAccuracy,
        int $expectedMagicAccuracy,
        int $expectedDefense,
        int $expectedMagicDefense,
        int $expectedEffectDuration
    ): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $statistics = new Statistic();

        $ability = $this->createAbilityByDataProvider($unit, 'Determination', $level);

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
        self::assertEquals($expectedDamageMultiplier, $unit->getOffense()->getDamageMultiplier());
        self::assertEquals($expectedAccuracy, $unit->getOffense()->getAccuracy());
        self::assertEquals($expectedMagicAccuracy, $unit->getOffense()->getMagicAccuracy());
        self::assertEquals($expectedDefense, $unit->getDefense()->getDefense());
        self::assertEquals($expectedMagicDefense, $unit->getDefense()->getMagicDefense());

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
                110,
                220,
                110,
                110,
                55,
                5,
            ],
            [
                2,
                113,
                225,
                112,
                112,
                56,
                5,
            ],
            [
                3,
                116,
                231,
                115,
                115,
                57,
                6,
            ],
            [
                4,
                119,
                238,
                119,
                119,
                59,
                6,
            ],
            [
                5,
                122,
                244,
                122,
                122,
                61,
                7,
            ],
        ];
    }
}
