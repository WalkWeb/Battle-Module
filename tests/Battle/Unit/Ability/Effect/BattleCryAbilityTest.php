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

class BattleCryAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/219.png" alt="" /> <span class="ability">Battle Cry</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/219.png" alt="" /> <span class="ability">Боевой клич</span>';

    /**
     * Тест на создание способности Battle Cry через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testBattleCryAbilityCreate(): void
    {
        $this->assertCreateEffectAbility(
            4,
            'Battle Cry',
            '/images/icons/ability/219.png',
            AbilityInterface::ACTIVATE_RAGE
        );
    }

    /**
     * Тест на применение способности Battle Cry
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedDamageMultiplier
     * @param int $expectedEffectDuration
     * @throws Exception
     */
    public function testBattleCryAbilityUse(
        int $level,
        int $expectedDamageMultiplier,
        int $expectedEffectDuration
    ): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $statistics = new Statistic();

        $ability = $this->getAbility($unit, 'Battle Cry', $level);

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
                120,
                5,
            ],
            [
                2,
                123,
                5,
            ],
            [
                3,
                126,
                6,
            ],
            [
                4,
                129,
                6,
            ],
            [
                5,
                132,
                7,
            ],
        ];
    }
}
