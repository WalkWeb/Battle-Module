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

class InaccessibilityAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/052.png" alt="" /> <span class="ability">Inaccessibility</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/052.png" alt="" /> <span class="ability">Неприступность</span>';

    /**
     * Тест на создание способности Inaccessibility через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testInaccessibilityAbilityCreate(): void
    {
        $this->assertCreateEffectAbility(
            4,
            'Inaccessibility',
            '/images/icons/ability/052.png',
            AbilityInterface::ACTIVATE_CUNNING
        );
    }

    /**
     * Тест на применение способности Inaccessibility
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedBlock
     * @param int $expectedMagicBlock
     * @param int $expectedEffectDuration
     * @throws Exception
     */
    public function testInaccessibilityAbilityUse(
        int $level,
        int $expectedBlock,
        int $expectedMagicBlock,
        int $expectedEffectDuration
    ): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $statistics = new Statistic();

        $ability = $this->createAbilityByDataProvider($unit, 'Inaccessibility', $level);

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
        self::assertEquals($expectedBlock, $unit->getDefense()->getBlock());
        self::assertEquals($expectedMagicBlock, $unit->getDefense()->getMagicBlock());

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
                12,
                12,
                5,
            ],
            [
                2,
                14,
                14,
                5,
            ],
            [
                3,
                16,
                16,
                6,
            ],
            [
                4,
                18,
                18,
                6,
            ],
            [
                5,
                20,
                20,
                7,
            ],
        ];
    }
}
