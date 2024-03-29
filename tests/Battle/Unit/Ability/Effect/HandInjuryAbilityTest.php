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

class HandInjuryAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/279.png" alt="" /> <span class="ability">Hand Injury</span> on <span style="color: #1e72e3">wounded_unit</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/279.png" alt="" /> <span class="ability">Повреждение рук</span> на <span style="color: #1e72e3">wounded_unit</span>';

    /**
     * Тест на создание способности Hand Injury через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testHandInjuryAbilityCreate(): void
    {
        $this->assertCreateEffectAbility(
            4,
            'Hand Injury',
            '/images/icons/ability/279.png',
            AbilityInterface::ACTIVATE_CUNNING
        );
    }

    /**
     * Тест на применение способности Hand Injury
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedDamage
     * @param int $expectedEffectDuration
     * @throws Exception
     */
    public function testHandInjuryAbilityUse(
        int $level,
        int $expectedDamage,
        int $expectedEffectDuration
    ): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $enemyUnit = UnitFactory::createByTemplate(11);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $statistics = new Statistic();

        $ability = $this->getAbility($unit, 'Hand Injury', $level);

        $this->activateAbility($ability, $unit);

        self::assertTrue($ability->isReady());

        // Изначальный урон противника
        self::assertEquals(35, $enemyUnit->getOffense()->getDamage($unit->getDefense()));

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
        self::assertEquals($expectedDamage, $enemyUnit->getOffense()->getDamage($unit->getDefense()));

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
                30,
                5,
            ],
            [
                2,
                29,
                5,
            ],
            [
                3,
                28,
                6,
            ],
            [
                4,
                28,
                6,
            ],
            [
                5,
                27,
                7,
            ],
        ];
    }
}
