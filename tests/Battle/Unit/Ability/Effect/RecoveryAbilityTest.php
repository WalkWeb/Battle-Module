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

class RecoveryAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_4</span> use <img src="/images/icons/ability/196.png" alt="" /> <span class="ability">Recovery</span> on <span style="color: #1e72e3">wounded_unit</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_4</span> использовал <img src="/images/icons/ability/196.png" alt="" /> <span class="ability">Восстановление</span> на <span style="color: #1e72e3">wounded_unit</span>';

    private const MESSAGE_EFFECT_EN = '<span style="color: #1e72e3">wounded_unit</span> restored %d life from effect <img src="/images/icons/ability/196.png" alt="" /> <span class="ability">Recovery</span>';
    private const MESSAGE_EFFECT_RU = '<span style="color: #1e72e3">wounded_unit</span> восстановил %d здоровья от эффекта <img src="/images/icons/ability/196.png" alt="" /> <span class="ability">Восстановление</span>';

    /**
     * Тест на создание способности Recovery через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testRecoveryAbilityCreate(): void
    {
        $name = 'Recovery';
        $icon = '/images/icons/ability/196.png';

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
     * Тест на применение способности Recovery
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedPower
     * @param int $expectedEffectDuration
     * @throws Exception
     */
    public function testRecoveryAbilityUse(int $level, int $expectedPower, int $expectedEffectDuration): void
    {
        $unit = UnitFactory::createByTemplate(4);
        $alliesUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$alliesUnit, $unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $statistics = new Statistic();

        $ability = $this->getAbility($unit, 'Recovery', $level);

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
                20,
                3,
            ],
            [
                2,
                27,
                4,
            ],
            [
                3,
                36,
                4,
            ],
            [
                4,
                47,
                5,
            ],
            [
                5,
                60,
                5,
            ],
        ];
    }
}
