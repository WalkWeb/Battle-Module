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

class SpiderWebAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/199.png" alt="" /> <span class="ability">Spider Web</span> on <span style="color: #1e72e3">unit_1</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/199.png" alt="" /> <span class="ability">Паучья сеть</span> на <span style="color: #1e72e3">unit_1</span>';

    /**
     * Тест на создание способности Spider Web через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testSpiderWebAbilityCreate(): void
    {
        $this->assertCreateEffectAbility(
            4,
            'Spider Web',
            '/images/icons/ability/199.png',
            AbilityInterface::ACTIVATE_CUNNING
        );
    }

    /**
     * Тест на применение способности Spider Web
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
    public function testSpiderWebAbilityUse(
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

        $ability = $this->getAbility($unit, 'Spider Web', $level);

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
        self::assertEquals($expectedAccuracy, $enemyUnit->getOffense()->getAccuracy());
        self::assertEquals($expectedMagicAccuracy, $enemyUnit->getOffense()->getMagicAccuracy());

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
                0.85,
                1.02,
                170,
                85,
                5,
            ],
            [
                2,
                0.83,
                1.00,
                166,
                83,
                5,
            ],
            [
                3,
                0.81,
                0.97,
                162,
                81,
                6,
            ],
            [
                4,
                0.79,
                0.95,
                158,
                79,
                6,
            ],
            [
                5,
                0.77,
                0.92,
                154,
                77,
                7,
            ],
        ];
    }
}
