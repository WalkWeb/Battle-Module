<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Damage;

use Battle\Action\DamageAction;
use Battle\Action\EffectAction;
use Battle\Action\ParalysisAction;
use Battle\Command\CommandFactory;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class StunningStrikeAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">Paladin</span> use <img src="/images/icons/ability/181.png" alt="" /> <span class="ability">Stunning Strike</span> and hit for %d damage against <span style="color: #1e72e3">unit_2</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">Paladin</span> использовал <img src="/images/icons/ability/181.png" alt="" /> <span class="ability">Оглушающий удар</span> и нанес удар на %d урона по <span style="color: #1e72e3">unit_2</span>';

    private const MESSAGE_STUN_EN = '<span style="color: #1e72e3">unit_2</span> stunned and unable to move';
    private const MESSAGE_STUN_RU = '<span style="color: #1e72e3">unit_2</span> оглушен и не может двигаться';

    /**
     * Тест на создание способности Stunning Strike через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testStunningStrikeAbilityCreate(): void
    {
        $name = 'Stunning Strike';
        $icon = '/images/icons/ability/181.png';
        $disposable = false;

        $unit = UnitFactory::createByTemplate(54);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, $name, 1);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertEquals($disposable, $ability->isDisposable());
        self::assertFalse($ability->isUsage());
        self::assertEquals(AbilityInterface::ACTIVATE_CUNNING, $ability->getTypeActivate());
        self::assertEquals(
            [
                WeaponTypeInterface::MACE,
                WeaponTypeInterface::TWO_HAND_MACE,
                WeaponTypeInterface::HEAVY_TWO_HAND_MACE,
            ],
            $ability->getAllowedWeaponTypes()
        );

        $actions = $ability->getActions($enemyCommand, $command);

        self::assertCount(2, $actions);

        foreach ($ability->getActions($enemyCommand, $command) as $i => $action) {
            if ($i === 0) {
                self::assertInstanceOf(DamageAction::class, $action);
                self::assertEquals($name, $action->getNameAction());
                self::assertEquals($icon, $action->getIcon());
                // Проверка отсутствия конвертации физического урона
                self::assertTrue($action->getOffense()->getPhysicalDamage() > 0);
            }
            if ($i === 1) {
                self::assertInstanceOf(EffectAction::class, $action);
                self::assertEquals($name, $action->getNameAction());
                self::assertEquals($icon, $action->getIcon());

                $effects = $action->getEffect()->getOnNextRoundActions();

                self::assertCount(1, $effects);

                foreach ($effects as $effect) {
                    self::assertInstanceOf(ParalysisAction::class, $effect);
                    self::assertEquals($name, $action->getNameAction());
                    self::assertEquals($icon, $action->getIcon());
                }
            }
        }
    }

    /**
     * Тест на применение способности Stunning Strike
     *
     * @dataProvider useDataProvider
     * @param int $level
     * @param int $expectedDamage
     * @param int $expectedAccuracy
     * @param int $expectedDuration
     * @throws Exception
     */
    public function testStunningStrikeAbilityUse(
        int $level,
        int $expectedDamage,
        int $expectedAccuracy,
        int $expectedDuration
    ): void
    {
        $unit = UnitFactory::createByTemplate(54);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, 'Stunning Strike', $level);

        $this->activateAbility($ability, $unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $i => $action) {
            if ($i === 0) {
                self::assertInstanceOf(DamageAction::class, $action);
                self::assertTrue($action->canByUsed());
                $action->handle();
                self::assertEquals($expectedDamage, $action->getFactualPower());
                self::assertEquals($expectedAccuracy, $action->getOffense()->getAccuracy());
                self::assertEquals(sprintf(self::MESSAGE_EN, $expectedDamage), $this->getChat()->addMessage($action));
                self::assertEquals(sprintf(self::MESSAGE_RU, $expectedDamage), $this->getChatRu()->addMessage($action));

                // Дополнительное проверяем, что по событию успешно создается анимация
                (new Scenario())->addAnimation($action, new Statistic());
            }
            if ($i === 1) {
                self::assertInstanceOf(EffectAction::class, $action);
                self::assertTrue($action->canByUsed());
                $action->handle();

                $effects = $enemyUnit->getEffects();

                self::assertCount(1, $effects);

                foreach ($effects as $effect) {
                    self::assertEquals($expectedDuration, $effect->getDuration());

                    $onNextRoundActions = $effect->getOnNextRoundActions();

                    self::assertCount(1, $onNextRoundActions);

                    foreach ($onNextRoundActions as $onNextRoundAction) {
                        self::assertInstanceOf(ParalysisAction::class, $onNextRoundAction);

                        self::assertEquals(self::MESSAGE_STUN_EN, $this->getChat()->addMessage($onNextRoundAction));
                        self::assertEquals(self::MESSAGE_STUN_RU, $this->getChatRu()->addMessage($onNextRoundAction));
                    }
                }
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
                40,
                260,
                1,
            ],
            [
                2,
                45,
                280,
                1,
            ],
            [
                3,
                50,
                300,
                1,
            ],
            [
                4,
                55,
                320,
                1,
            ],
            [
                5,
                60,
                340,
                2,
            ],
        ];
    }
}
