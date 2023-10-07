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

class GoodPositionAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/279.png" alt="" /> <span class="ability">Good Position</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/279.png" alt="" /> <span class="ability">Хорошая позиция</span>';

    /**
     * Тест на создание способности Good Position через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testGoodPositionAbilityCreate(): void
    {
        $this->assertCreateEffectAbility(
            4,
            'Good Position',
            '/images/icons/ability/279.png',
            AbilityInterface::ACTIVATE_CUNNING
        );
    }

    /**
     * Тест на применение способности Good Position
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedAccuracy
     * @param int $expectedMagicAccuracy
     * @param int $expectedDefense
     * @param int $expectedMagicDefense
     * @param int $expectedEffectDuration
     * @throws Exception
     */
    public function testGoodPositionAbilityUse(
        int $level,
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

        $ability = $this->createAbilityByDataProvider($unit, 'Good Position', $level);

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
                227,
                113,
                113,
                56,
                5,
            ],
            [
                2,
                231,
                115,
                115,
                57,
                5,
            ],
            [
                3,
                236,
                118,
                118,
                59,
                6,
            ],
            [
                4,
                240,
                120,
                120,
                60,
                6,
            ],
            [
                5,
                244,
                122,
                122,
                61,
                7,
            ],
        ];
    }
}
