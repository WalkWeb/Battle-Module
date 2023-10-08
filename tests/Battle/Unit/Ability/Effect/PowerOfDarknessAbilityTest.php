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

class PowerOfDarknessAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/226.png" alt="" /> <span class="ability">Power of Darkness</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/226.png" alt="" /> <span class="ability">Сила тьмы</span>';

    /**
     * Тест на создание способности Power of Darkness через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testPowerOfDarknessAbilityCreate(): void
    {
        $this->assertCreateEffectAbility(
            4,
            'Power of Darkness',
            '/images/icons/ability/226.png',
            AbilityInterface::ACTIVATE_CUNNING
        );
    }

    /**
     * Тест на применение способности Power of Darkness
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param float $expectedAttackSpeed
     * @param float $expectedCastSpeed
     * @param int $expectedAccuracy
     * @param int $expectedMagicAccuracy
     * @param int $expectedEffectDuration
     * @throws Exception
     */
    public function testPowerOfDarknessAbilityUse(
        int $level,
        float $expectedAttackSpeed,
        float $expectedCastSpeed,
        int $expectedAccuracy,
        int $expectedMagicAccuracy,
        int $expectedEffectDuration
    ): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $statistics = new Statistic();

        $ability = $this->getAbility($unit, 'Power of Darkness', $level);

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
        self::assertEquals($expectedAttackSpeed, $unit->getOffense()->getAttackSpeed());
        self::assertEquals($expectedCastSpeed, $unit->getOffense()->getCastSpeed());
        self::assertEquals($expectedAccuracy, $unit->getOffense()->getAccuracy());
        self::assertEquals($expectedMagicAccuracy, $unit->getOffense()->getMagicAccuracy());

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
                1.1,
                1.32,
                240,
                120,
                5,
            ],
            [
                2,
                1.13,
                1.36,
                248,
                124,
                5,
            ],
            [
                3,
                1.16,
                1.39,
                256,
                128,
                6,
            ],
            [
                4,
                1.19,
                1.43,
                264,
                132,
                6,
            ],
            [
                5,
                1.22,
                1.46,
                272,
                136,
                7,
            ],
        ];
    }
}
