<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Summon;

use Battle\Action\ActionInterface;
use Battle\Response\Scenario\Scenario;
use Battle\Response\Statistic\Statistic;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Battle\Action\SummonAction;
use Battle\Command\CommandFactory;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;
use Battle\Unit\Ability\AbilityCollection;

class SummonSkeletonAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> summon <img src="/images/icons/ability/338.png" alt="" /> <span class="ability">Skeleton</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> призвал <img src="/images/icons/ability/338.png" alt="" /> <span class="ability">Скелета</span>';

    // -----------------------------------------------------------------------------------------------------------------
    // ------------------------------------------   Тесты через Ability   ----------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание и применение способности SummonSkeletonAbility через универсальный объект Ability
     *
     * @throws Exception
     */
    public function testSummonSkeletonAbilityUse(): void
    {
        $name = 'Skeleton';
        $icon = '/images/icons/ability/338.png';
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new Ability(
            $unit,
            false,
            $name,
            $icon,
            [
                [
                    'type'   => ActionInterface::SUMMON,
                    'name'   => $name,
                    'icon'   => $icon,
                    'summon' => [
                        'name'                         => $name,
                        'level'                        => 1,
                        'avatar'                       => '/images/avas/monsters/003.png',
                        'life'                         => 38,
                        'total_life'                   => 38,
                        'mana'                         => 0,
                        'total_mana'                   => 0,
                        'melee'                        => true,
                        'class'                        => null,
                        'race'                         => 8,
                        'add_concentration_multiplier' => 0,
                        'cunning_multiplier'           => 0,
                        'add_rage_multiplier'          => 0,
                        'offense'                      => [
                            'damage_type'         => 1,
                            'weapon_type'         => WeaponTypeInterface::SWORD,
                            'physical_damage'     => 16,
                            'fire_damage'         => 0,
                            'water_damage'        => 0,
                            'air_damage'          => 0,
                            'earth_damage'        => 0,
                            'life_damage'         => 0,
                            'death_damage'        => 0,
                            'attack_speed'        => 1,
                            'cast_speed'          => 0,
                            'accuracy'            => 200,
                            'magic_accuracy'      => 100,
                            'block_ignoring'      => 0,
                            'critical_chance'     => 5,
                            'critical_multiplier' => 150,
                            'damage_multiplier'   => 100,
                            'vampirism'           => 0,
                            'magic_vampirism'     => 0,
                        ],
                        'defense'                      => [
                            'physical_resist'     => 0,
                            'fire_resist'         => 0,
                            'water_resist'        => 0,
                            'air_resist'          => 0,
                            'earth_resist'        => 0,
                            'life_resist'         => 0,
                            'death_resist'        => 0,
                            'defense'             => 100,
                            'magic_defense'       => 50,
                            'block'               => 0,
                            'magic_block'         => 0,
                            'mental_barrier'      => 0,
                            'max_physical_resist' => 75,
                            'max_fire_resist'     => 75,
                            'max_water_resist'    => 75,
                            'max_air_resist'      => 75,
                            'max_earth_resist'    => 75,
                            'max_life_resist'     => 75,
                            'max_death_resist'    => 75,
                            'global_resist'       => 0,
                            'dodge'               => 0,
                        ],
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

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(SummonAction::class, $action);
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
        self::assertEquals(0, $unit->getConcentration());
    }

    // -----------------------------------------------------------------------------------------------------------------
    // -------------------------------   Аналогичные тесты через AbilityDataProvider   ---------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Тест на создание и применение способности SummonSkeletonAbility через AbilityDataProvider
     *
     * @throws Exception
     */
    public function testSummonSkeletonAbilityDataProviderUse(): void
    {
        $name = 'Skeleton';
        $icon = '/images/icons/ability/338.png';
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = $this->getAbility($unit, 'Skeleton');

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

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);

        self::assertTrue($ability->isReady());

        $actions = $ability->getActions($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertInstanceOf(SummonAction::class, $action);
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));
        }

        $ability->usage();

        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
        self::assertEquals(0, $unit->getConcentration());
    }
}
