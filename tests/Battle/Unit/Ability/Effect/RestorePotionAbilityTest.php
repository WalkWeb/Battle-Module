<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\EffectAction;
use Battle\Action\HealAction;
use Battle\Action\ManaRestoreAction;
use Battle\Command\CommandFactory;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Battle\Unit\Ability\AbilityInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class RestorePotionAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/234.png" alt="" /> <span class="ability">Restore Potion</span> on <span style="color: #1e72e3">wounded_unit</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/234.png" alt="" /> <span class="ability">Зелье оздоровления</span> на <span style="color: #1e72e3">wounded_unit</span>';

    private const MESSAGE_HEAL_EFFECT_EN = '<span style="color: #1e72e3">wounded_unit</span> restored %d life from effect <img src="/images/icons/ability/234.png" alt="" /> <span class="ability">Restore Potion</span>';
    private const MESSAGE_HEAL_EFFECT_RU = '<span style="color: #1e72e3">wounded_unit</span> восстановил %d здоровья от эффекта <img src="/images/icons/ability/234.png" alt="" /> <span class="ability">Зелье оздоровления</span>';

    private const MESSAGE_MANA_EFFECT_EN = '<span style="color: #1e72e3">wounded_unit</span> restored %d mana from effect <img src="/images/icons/ability/234.png" alt="" /> <span class="ability">Restore Potion</span>';
    private const MESSAGE_MANA_EFFECT_RU = '<span style="color: #1e72e3">wounded_unit</span> восстановил %d маны от эффекта <img src="/images/icons/ability/234.png" alt="" /> <span class="ability">Зелье оздоровления</span>';

    /**
     * Тест на создание способности Recovery через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testRestorePotionAbilityCreate(): void
    {
        $name = 'Restore Potion';
        $icon = '/images/icons/ability/234.png';

        $unit = UnitFactory::createByTemplate(4);
        $alliesUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit, $alliesUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, $name, 1);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertFalse($ability->isDisposable());
        self::assertFalse($ability->isUsage());
        self::assertEquals(AbilityInterface::ACTIVATE_CUNNING, $ability->getTypeActivate());
        self::assertEquals([], $ability->getAllowedWeaponTypes());

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(1, $actions);

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertInstanceOf(EffectAction::class, $action);

            $onNextRoundActions = $action->getEffect()->getOnNextRoundActions();

            self::assertCount(2, $onNextRoundActions);

            foreach ($onNextRoundActions as $i => $effectAction) {
                if ($i === 0) {
                    self::assertInstanceOf(HealAction::class, $effectAction);
                    self::assertEquals($name, $action->getNameAction());
                    self::assertEquals($icon, $action->getIcon());
                }
                if ($i === 1) {
                    self::assertInstanceOf(ManaRestoreAction::class, $effectAction);
                    self::assertEquals($name, $action->getNameAction());
                    self::assertEquals($icon, $action->getIcon());
                }
            }
        }
    }

    /**
     * Тест на применение способности Recovery
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedHealPower
     * @param int $expectedManaPower
     * @param int $expectedEffectDuration
     * @throws Exception
     */
    public function testRestorePotionAbilityUse(int $level, int $expectedHealPower, int $expectedManaPower, int $expectedEffectDuration): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $alliesUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$alliesUnit, $unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $statistics = new Statistic();

        $ability = $this->createAbilityByDataProvider($unit, 'Restore Potion', $level);

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

            // Проверяем эффект
            self::assertCount(1, $alliesUnit->getEffects());
            self::assertCount(2, $alliesUnit->getBeforeActions());

            foreach ($alliesUnit->getEffects() as $effect) {
                self::assertEquals($expectedEffectDuration, $effect->getBaseDuration());
            }

            $i = 0;
            foreach ($alliesUnit->getBeforeActions() as $effectAction) {
                if ($i === 0) {
                    self::assertInstanceOf(HealAction::class, $effectAction);
                    self::assertTrue($effectAction->canByUsed());
                    $effectAction->handle();
                    self::assertEquals($expectedHealPower, $effectAction->getFactualPower());
                    self::assertEquals(sprintf(self::MESSAGE_HEAL_EFFECT_EN, $expectedHealPower), $this->getChat()->addMessage($effectAction));
                    self::assertEquals(sprintf(self::MESSAGE_HEAL_EFFECT_RU, $expectedHealPower), $this->getChatRu()->addMessage($effectAction));
                }
                if ($i === 1) {
                    self::assertInstanceOf(ManaRestoreAction::class, $effectAction);
                    self::assertTrue($effectAction->canByUsed());
                    $effectAction->handle();
                    self::assertEquals($expectedManaPower, $effectAction->getFactualPower());
                    self::assertEquals(sprintf(self::MESSAGE_MANA_EFFECT_EN, $expectedManaPower), $this->getChat()->addMessage($effectAction));
                    self::assertEquals(sprintf(self::MESSAGE_MANA_EFFECT_RU, $expectedManaPower), $this->getChatRu()->addMessage($effectAction));
                }
                $i++;
            }
        }

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
                10,
                7,
                5,
            ],
            [
                2,
                17,
                9,
                5,
            ],
            [
                3,
                28,
                12,
                6,
            ],
            [
                4,
                40,
                17,
                6,
            ],
            [
                5,
                48,
                21,
                7,
            ],
        ];
    }
}
