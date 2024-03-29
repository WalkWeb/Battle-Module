<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class IncinerationAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/232.png" alt="" /> <span class="ability">Incineration</span> on <span style="color: #1e72e3">unit_2</span> and <span style="color: #1e72e3">unit_3</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/232.png" alt="" /> <span class="ability">Испепеление</span> на <span style="color: #1e72e3">unit_2</span> и <span style="color: #1e72e3">unit_3</span>';

    // -----------------------------------------------------------------------------------------------------------------
    // ------------------------------------------   Тесты через Ability   ----------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @throws Exception
     */
    public function testIncinerationAbility(): void
    {
        $name = 'Incineration';
        $icon = '/images/icons/ability/232.png';

        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit]);

        $ability = new Ability(
            $unit,
            false,
            $name,
            $icon,
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'type_target'    => ActionInterface::TARGET_ALL_ENEMY,
                    'name'           => $name,
                    'icon'           => $icon,
                    'message_method' => 'applyEffect',
                    'effect'         => [
                        'name'                  => $name,
                        'icon'                  => $icon,
                        'duration'              => 8,
                        'on_apply_actions'      => [],
                        'on_next_round_actions' => [
                            [
                                'type'             => ActionInterface::DAMAGE,
                                'type_target'      => ActionInterface::TARGET_SELF,
                                'name'             => $name,
                                'offense'          => [
                                    'damage_type'         => 2,
                                    'weapon_type'         => WeaponTypeInterface::NONE,
                                    'physical_damage'     => 6,
                                    'fire_damage'         => 0,
                                    'water_damage'        => 0,
                                    'air_damage'          => 0,
                                    'earth_damage'        => 0,
                                    'life_damage'         => 0,
                                    'death_damage'        => 0,
                                    'attack_speed'        => 0,
                                    'cast_speed'          => 1,
                                    'accuracy'            => 500,
                                    'magic_accuracy'      => 500,
                                    'block_ignoring'      => 0,
                                    'critical_chance'     => 0,
                                    'critical_multiplier' => 0,
                                    'damage_multiplier'   => 100,
                                    'vampirism'           => 0,
                                    'magic_vampirism'     => 0,
                                ],
                                'can_be_avoided'   => false,
                                'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                                'message_method'   => DamageAction::EFFECT_MESSAGE_METHOD,
                                'icon'             => $icon,
                            ],
                        ],
                        'on_disable_actions'    => [],
                    ],
                ],
            ],
            AbilityInterface::ACTIVATE_CONCENTRATION,
            [],
            0
        );

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertFalse($ability->isDisposable());
        self::assertFalse($ability->isUsage());

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);
        $collection->update($unit);

        self::assertTrue($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        // Проверяем, что перед использованием способности вражеские юниты не имеют эффекта
        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            foreach ($enemyCommand->getUnits() as $unit) {
                self::assertFalse($unit->getEffects()->exist($action->getEffect()));
            }
            self::assertTrue($action->canByUsed());
            $action->handle();

            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));
        }

        $ability->usage();

        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Проверяем, что после использования способности вражеские юниты имеют эффект
        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            foreach ($enemyCommand->getUnits() as $unit) {
                self::assertTrue($unit->getEffects()->exist($action->getEffect()));
            }
            self::assertFalse($action->canByUsed());
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // -------------------------------   Аналогичные тесты через AbilityDataProvider   ---------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @throws Exception
     */
    public function testIncinerationAbilityDataProvider(): void
    {
        $name = 'Incineration';
        $icon = '/images/icons/ability/232.png';

        $unit = UnitFactory::createByTemplate(1);
        $firstEnemyUnit = UnitFactory::createByTemplate(2);
        $secondaryEnemyUnit = UnitFactory::createByTemplate(3);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$firstEnemyUnit, $secondaryEnemyUnit]);

        $ability = $this->getAbility($unit, 'Incineration');

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertFalse($ability->isDisposable());
        self::assertFalse($ability->isUsage());

        // Up concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);
        $collection->update($unit);

        self::assertTrue($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        // Проверяем, что перед использованием способности вражеские юниты не имеют эффекта
        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            foreach ($enemyCommand->getUnits() as $unit) {
                self::assertFalse($unit->getEffects()->exist($action->getEffect()));
            }
            self::assertTrue($action->canByUsed());
            $action->handle();

            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));

            // Дополнительное проверяем, что по событию успешно создается анимация
            (new Scenario())->addAnimation($action, new Statistic());
        }

        $ability->usage();

        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Проверяем, что после использования способности вражеские юниты имеют эффект
        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            foreach ($enemyCommand->getUnits() as $unit) {
                self::assertTrue($unit->getEffects()->exist($action->getEffect()));
            }
            self::assertFalse($action->canByUsed());
        }
    }
}
