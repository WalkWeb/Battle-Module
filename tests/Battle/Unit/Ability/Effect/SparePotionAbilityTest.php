<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\EffectAction;
use Battle\Action\HealAction;
use Battle\Command\CommandFactory;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Battle\Unit\Ability\AbilityInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class SparePotionAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/299.png" alt="" /> <span class="ability">Spare Potion</span> on <span style="color: #1e72e3">wounded_unit</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/299.png" alt="" /> <span class="ability">Запасное зелье</span> на <span style="color: #1e72e3">wounded_unit</span>';

    private const MESSAGE_EFFECT_EN = '<span style="color: #1e72e3">wounded_unit</span> restored %d life from effect <img src="/images/icons/ability/299.png" alt="" /> <span class="ability">Spare Potion</span>';
    private const MESSAGE_EFFECT_RU = '<span style="color: #1e72e3">wounded_unit</span> восстановил %d здоровья от эффекта <img src="/images/icons/ability/299.png" alt="" /> <span class="ability">Запасное зелье</span>';

    /**
     * Тест на создание способности Spare Potion через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testSparePotionAbilityCreate(): void
    {
        $name = 'Spare Potion';
        $icon = '/images/icons/ability/299.png';

        $unit = UnitFactory::createByTemplate(4);
        $alliesUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit, $alliesUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, $name, 1);

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

        foreach ($ability->getActions($enemyCommand, $command) as $i => $action) {
            self::assertInstanceOf(EffectAction::class, $action);
            foreach ($action->getEffect()->getOnNextRoundActions() as $effectDamage) {
                self::assertInstanceOf(HealAction::class, $effectDamage);
                self::assertEquals($name, $action->getNameAction());
                self::assertEquals($icon, $action->getIcon());
            }
        }
    }

    /**
     * Тест на применение способности Spare Potion
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedPower
     * @param int $expectedEffectDuration
     * @throws Exception
     */
    public function testSparePotionAbilityUse(int $level, int $expectedPower, int $expectedEffectDuration): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $alliesUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$alliesUnit, $unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $statistics = new Statistic();

        $ability = $this->getAbility($unit, 'Spare Potion', $level);

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
            self::assertCount(1, $alliesUnit->getBeforeActions());

            foreach ($alliesUnit->getEffects() as $effect) {
                self::assertEquals($expectedEffectDuration, $effect->getBaseDuration());
            }

            foreach ($alliesUnit->getBeforeActions() as $effectAction) {
                self::assertInstanceOf(HealAction::class, $effectAction);
                self::assertTrue($effectAction->canByUsed());
                $effectAction->handle();
                self::assertEquals($expectedPower, $effectAction->getFactualPower());
                self::assertEquals(sprintf(self::MESSAGE_EFFECT_EN, $expectedPower), $this->getChat()->addMessage($effectAction));
                self::assertEquals(sprintf(self::MESSAGE_EFFECT_RU, $expectedPower), $this->getChatRu()->addMessage($effectAction));
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
                4,
            ],
            [
                2,
                17,
                4,
            ],
            [
                3,
                28,
                5,
            ],
            [
                4,
                39,
                5,
            ],
            [
                5,
                52,
                6,
            ],
        ];
    }
}
