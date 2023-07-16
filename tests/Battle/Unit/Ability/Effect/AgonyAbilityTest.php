<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\DamageAction;
use Battle\Action\EffectAction;
use Battle\Command\CommandFactory;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class AgonyAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/083.png" alt="" /> <span class="ability">Agony</span> on <span style="color: #1e72e3">unit_2</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/083.png" alt="" /> <span class="ability">Агония</span> на <span style="color: #1e72e3">unit_2</span>';

    private const MESSAGE_EFFECT_EN = '<span style="color: #1e72e3">unit_2</span> received %d damage from effect <img src="/images/icons/ability/083.png" alt="" /> <span class="ability">Agony</span>';
    private const MESSAGE_EFFECT_RU = '<span style="color: #1e72e3">unit_2</span> получил %d урона от эффекта <img src="/images/icons/ability/083.png" alt="" /> <span class="ability">Агония</span>';

    /**
     * Тест на создание способности Agony через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testAgonyAbilityCreate(): void
    {
        $name = 'Agony';
        $icon = '/images/icons/ability/083.png';
        $disposable = false;

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->createAbilityByDataProvider($unit, $name, 1);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertEquals($disposable, $ability->isDisposable());
        self::assertFalse($ability->isUsage());
        self::assertEquals(AbilityInterface::ACTIVATE_CONCENTRATION, $ability->getTypeActivate());
        self::assertEquals([
            WeaponTypeInterface::SWORD,
            WeaponTypeInterface::AXE,
            WeaponTypeInterface::MACE,
            WeaponTypeInterface::TWO_HAND_SWORD,
            WeaponTypeInterface::TWO_HAND_AXE,
            WeaponTypeInterface::TWO_HAND_MACE,
            WeaponTypeInterface::HEAVY_TWO_HAND_SWORD,
            WeaponTypeInterface::HEAVY_TWO_HAND_AXE,
            WeaponTypeInterface::HEAVY_TWO_HAND_MACE,
            WeaponTypeInterface::SPEAR,
            WeaponTypeInterface::LANCE,
            WeaponTypeInterface::DAGGER,
        ],
            $ability->getAllowedWeaponTypes()
        );

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(1, $actions);

        foreach ($ability->getActions($enemyCommand, $command) as $i => $action) {
            self::assertInstanceOf(EffectAction::class, $action);
            foreach ($action->getEffect()->getOnNextRoundActions() as $effectDamage) {
                self::assertInstanceOf(DamageAction::class, $effectDamage);
                self::assertEquals($name, $action->getNameAction());
                self::assertEquals($icon, $action->getIcon());
                // Проверка конвертации физического урона в урон магии смерти
                self::assertTrue($effectDamage->getOffense()->getDeathDamage() > 0);
            }
        }
    }

    /**
     * Тест на применение способности Agony
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedEffectDamage
     * @param int $expectedEffectDuration
     * @throws Exception
     */
    public function testAgonyAbilityUse(int $level, int $expectedEffectDamage, int $expectedEffectDuration): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $statistics = new Statistic();

        $ability = $this->createAbilityByDataProvider($unit, 'Agony', $level);

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
            self::assertCount(1, $enemyUnit->getEffects());
            self::assertCount(1, $enemyUnit->getBeforeActions());

            foreach ($enemyUnit->getEffects() as $effect) {
                self::assertEquals($expectedEffectDuration, $effect->getBaseDuration());
            }

            foreach ($enemyUnit->getBeforeActions() as $effectAction) {
                self::assertInstanceOf(DamageAction::class, $effectAction);
                self::assertTrue($effectAction->canByUsed());
                $effectAction->handle();
                self::assertEquals($expectedEffectDamage, $effectAction->getFactualPower());
                self::assertEquals(sprintf(self::MESSAGE_EFFECT_EN, $expectedEffectDamage), $this->getChat()->addMessage($effectAction));
                self::assertEquals(sprintf(self::MESSAGE_EFFECT_RU, $expectedEffectDamage), $this->getChatRu()->addMessage($effectAction));
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
                8,
                4,
            ],
            [
                2,
                8,
                5,
            ],
            [
                3,
                8,
                5,
            ],
            [
                4,
                9,
                6,
            ],
            [
                5,
                9,
                6,
            ],
        ];
    }
}
