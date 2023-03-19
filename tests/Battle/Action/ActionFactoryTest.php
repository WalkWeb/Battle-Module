<?php

declare(strict_types=1);

namespace Tests\Battle\Action;

use Battle\Action\ActionException;
use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Action\BuffAction;
use Battle\Action\DamageAction;
use Battle\Action\EffectAction;
use Battle\Action\HealAction;
use Battle\Action\ManaRestoreAction;
use Battle\Action\ParalysisAction;
use Battle\Action\ResurrectionAction;
use Battle\Action\SummonAction;
use Battle\Action\WaitAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Container\Container;
use Battle\Unit\Offense\OffenseFactory;
use Battle\Unit\UnitException;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\BaseFactory;
use Tests\Factory\UnitFactory as TestUnitFactory;
use Tests\Factory\UnitFactoryException;
use Battle\Unit\UnitFactory;

class ActionFactoryTest extends AbstractUnitTest
{
    /**
     * Тест на успешное создание DamageAction на основе массива с данными
     *
     * @throws Exception
     */
    public function testActionFactoryCreateDamageSuccess(): void
    {
        $container = $this->getContainer();
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2, $container);

        $offenseData = [
            'damage_type'         => 1,
            'weapon_type'         => WeaponTypeInterface::SWORD,
            'physical_damage'     => 35,
            'fire_damage'         => 0,
            'water_damage'        => 0,
            'air_damage'          => 0,
            'earth_damage'        => 0,
            'life_damage'         => 0,
            'death_damage'        => 0,
            'attack_speed'        => 1.2,
            'cast_speed'          => 0,
            'accuracy'            => 176,
            'magic_accuracy'      => 12,
            'block_ignoring'      => 0,
            'critical_chance'     => 5,
            'critical_multiplier' => 200,
            'damage_multiplier'   => 100,
            'vampirism'           => 0,
            'magic_vampirism'     => 0,
        ];

        // Вариант с минимальным набором данных
        $data = [
            'type'             => ActionInterface::DAMAGE,
            'action_unit'      => $unit,
            'enemy_command'    => $enemyCommand,
            'allies_command'   => $command,
            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
            'offense'          => $offenseData,
            'can_be_avoided'   => $canBeAvoided = true,
            'name'             => $name = 'attack',
            'animation_method' => $animationMethod = 'animation test',
            'message_method'   => $messageMethod = 'message test',
        ];

        $action = $container->getActionFactory()->create($data);

        self::assertInstanceOf(DamageAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_RANDOM_ENEMY, $action->getTypeTarget());
        self::assertEquals(OffenseFactory::create($offenseData, $container), $action->getOffense());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals('', $action->getIcon());
        self::assertEquals($canBeAvoided, $action->isCanBeAvoided());
        self::assertEquals($animationMethod, $action->getAnimationMethod());
        self::assertEquals($messageMethod, $action->getMessageMethod());

        // Полный набор данных (добавляется только icon)
        $data = [
            'type'             => ActionInterface::DAMAGE,
            'action_unit'      => $unit,
            'enemy_command'    => $enemyCommand,
            'allies_command'   => $command,
            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
            'offense'          => $offenseData,
            'can_be_avoided'   => $canBeAvoided = false,
            'name'             => $name = 'action name 123',
            'animation_method' => $animationMethod = 'effectDamage',
            'message_method'   => $messageMethod = 'damageAbility',
            'icon'             => $icon = 'icon.png',
        ];

        $action = $container->getActionFactory()->create($data);

        self::assertInstanceOf(DamageAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_RANDOM_ENEMY, $action->getTypeTarget());
        self::assertEquals(OffenseFactory::create($offenseData, $container), $action->getOffense());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($animationMethod, $action->getAnimationMethod());
        self::assertEquals($messageMethod, $action->getMessageMethod());
        self::assertEquals($icon, $action->getIcon());
        self::assertEquals($canBeAvoided, $action->isCanBeAvoided());
    }

    /**
     * Тест на успешное создание DamageAction с переданным multiple_offense
     *
     * @throws Exception
     */
    public function testActionFactoryCreateMultipleDamageSuccess(): void
    {
        $container = $this->getContainer();
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2, $container);

        $multipleOffense = [
            'damage'              => 2.0,
            'speed'               => 2.5,
            'accuracy'            => 3.0,
            'critical_chance'     => 3.5,
            'critical_multiplier' => 4.0,
        ];

        // Вариант с минимальным набором данных
        $data = [
            'type'             => ActionInterface::DAMAGE,
            'action_unit'      => $unit,
            'enemy_command'    => $enemyCommand,
            'allies_command'   => $command,
            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
            'multiple_offense' => $multipleOffense,
            'can_be_avoided'   => $canBeAvoided = true,
            'name'             => $name = 'attack',
            'animation_method' => $animationMethod = 'animation test',
            'message_method'   => $messageMethod = 'message test',
        ];

        $action = $container->getActionFactory()->create($data);

        // Проверяем, что Offense взялся на основе параметров атакующего юнита, но был изменены в %
        self::assertEquals(
            (int)($unit->getOffense()->getPhysicalDamage() * $multipleOffense['damage']),
            $action->getOffense()->getPhysicalDamage()
        );
        self::assertEquals(
            (int)($unit->getOffense()->getFireDamage() * $multipleOffense['damage']),
            $action->getOffense()->getFireDamage()
        );
        self::assertEquals(
            (int)($unit->getOffense()->getWaterDamage() * $multipleOffense['damage']),
            $action->getOffense()->getWaterDamage()
        );
        self::assertEquals(
            (int)($unit->getOffense()->getAirDamage() * $multipleOffense['damage']),
            $action->getOffense()->getAirDamage()
        );
        self::assertEquals(
            (int)($unit->getOffense()->getEarthDamage() * $multipleOffense['damage']),
            $action->getOffense()->getEarthDamage()
        );
        self::assertEquals(
            (int)($unit->getOffense()->getLifeDamage() * $multipleOffense['damage']),
            $action->getOffense()->getLifeDamage()
        );
        self::assertEquals(
            (int)($unit->getOffense()->getDeathDamage() * $multipleOffense['damage']),
            $action->getOffense()->getDeathDamage()
        );
        self::assertEquals(
            round($unit->getOffense()->getAttackSpeed() * $multipleOffense['speed'], 2),
            $action->getOffense()->getAttackSpeed()
        );
        self::assertEquals(
            round($unit->getOffense()->getCastSpeed() * $multipleOffense['speed'], 2),
            $action->getOffense()->getCastSpeed()
        );
        self::assertEquals(
            (int)($unit->getOffense()->getAccuracy() * $multipleOffense['accuracy']),
            $action->getOffense()->getAccuracy()
        );
        self::assertEquals(
            (int)($unit->getOffense()->getMagicAccuracy() * $multipleOffense['accuracy']),
            $action->getOffense()->getMagicAccuracy()
        );
        self::assertEquals(
            (int)($unit->getOffense()->getCriticalChance() * $multipleOffense['critical_chance']),
            $action->getOffense()->getCriticalChance()
        );
        self::assertEquals(
            (int)($unit->getOffense()->getCriticalMultiplier() * $multipleOffense['critical_multiplier']),
            $action->getOffense()->getCriticalMultiplier()
        );
    }

    /**
     * Тест на успешное создание HealAction на основе массива с данными
     *
     * @throws Exception
     */
    public function testActionFactoryCreateHealSuccess(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);
        $container = $this->getContainer();

        $data = [
            'type'             => ActionInterface::HEAL,
            'action_unit'      => $unit,
            'enemy_command'    => $enemyCommand,
            'allies_command'   => $command,
            'type_target'      => ActionInterface::TARGET_WOUNDED_ALLIES,
            'power'            => $power = 50,
            'name'             => $name = 'action name 123',
            'animation_method' => $animationMethod = 'effectHeal',
            'message_method'   => $messageMethod = 'heal',
            'icon'             => $icon = 'icon.png',
        ];

        $action = $container->getActionFactory()->create($data);

        self::assertInstanceOf(HealAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_WOUNDED_ALLIES, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($animationMethod, $action->getAnimationMethod());
        self::assertEquals($messageMethod, $action->getMessageMethod());
        self::assertEquals($icon, $action->getIcon());
    }

    /**
     * Тест на успешное создание WaitAction на основе массива с данными
     *
     * @throws Exception
     */
    public function testActionFactoryCreateWaitSuccess(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $data = [
            'type'           => ActionInterface::WAIT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
        ];

        $action = $this->getContainer()->getActionFactory()->create($data);

        self::assertInstanceOf(WaitAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_SELF, $action->getTypeTarget());
        self::assertEquals('', $action->getNameAction());
    }

    /**
     * Тест на успешное создание ParalysisAction на основе массива с данными
     *
     * @throws Exception
     */
    public function testActionFactoryCreateParalysisSuccess(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $data = [
            'type'           => ActionInterface::PARALYSIS,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'message_method' => ParalysisAction::PARALYSIS_MESSAGE_METHOD,
        ];

        $action = $this->getContainer()->getActionFactory()->create($data);

        self::assertInstanceOf(ParalysisAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_SELF, $action->getTypeTarget());
        self::assertEquals('', $action->getNameAction());
    }

    /**
     * Тест на успешное создание SummonAction на основе массива с данными
     *
     * @throws Exception
     */
    public function testActionFactoryCreateSummonSuccess(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $summonData = [
            'name'                         => 'Imp',
            'level'                        => 1,
            'avatar'                       => '/images/avas/monsters/004.png',
            'block_ignoring'               => 0,
            'life'                         => 30,
            'total_life'                   => 30,
            'mana'                         => 0,
            'total_mana'                   => 0,
            'melee'                        => true,
            'class'                        => 1,
            'race'                         => 9,
            'add_concentration_multiplier' => 0,
            'add_rage_multiplier'          => 0,
            'offense'                      => [
                'damage_type'         => 1,
                'weapon_type'         => WeaponTypeInterface::SWORD,
                'physical_damage'     => 10,
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
        ];

        $data = [
            'type'           => ActionInterface::SUMMON,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'name'           => 'Summon Imp',
            'summon'         => $summonData,
        ];

        $action = $this->getContainer()->getActionFactory()->create($data);

        self::assertInstanceOf(SummonAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_SELF, $action->getTypeTarget());
        self::assertEquals('Summon Imp', $action->getNameAction());

        self::assertEquals($summonData['name'], $action->getSummonUnit()->getName());
        self::assertEquals($summonData['level'], $action->getSummonUnit()->getLevel());
        self::assertEquals($summonData['avatar'], $action->getSummonUnit()->getAvatar());
        self::assertEquals($summonData['life'], $action->getSummonUnit()->getLife());
        self::assertEquals($summonData['total_life'], $action->getSummonUnit()->getTotalLife());
        self::assertEquals($summonData['melee'], $action->getSummonUnit()->isMelee());
        self::assertEquals($summonData['class'], $action->getSummonUnit()->getClass()->getId());
        self::assertEquals($summonData['race'], $action->getSummonUnit()->getRace()->getId());

        self::assertEquals($summonData['offense']['damage_type'], $action->getSummonUnit()->getOffense()->getDamageType());
        self::assertEquals($summonData['offense']['weapon_type'], $action->getSummonUnit()->getOffense()->getWeaponType()->getId());
        self::assertEquals($summonData['offense']['physical_damage'], $action->getSummonUnit()->getOffense()->getPhysicalDamage());
        self::assertEquals($summonData['offense']['attack_speed'], $action->getSummonUnit()->getOffense()->getAttackSpeed());
        self::assertEquals($summonData['offense']['cast_speed'], $action->getSummonUnit()->getOffense()->getCastSpeed());
        self::assertEquals($summonData['offense']['accuracy'], $action->getSummonUnit()->getOffense()->getAccuracy());
        self::assertEquals($summonData['offense']['magic_accuracy'], $action->getSummonUnit()->getOffense()->getMagicAccuracy());
        self::assertEquals($summonData['offense']['block_ignoring'], $action->getSummonUnit()->getOffense()->getBlockIgnoring());

        self::assertEquals($summonData['defense']['physical_resist'], $action->getSummonUnit()->getDefense()->getPhysicalResist());
        self::assertEquals($summonData['defense']['defense'], $action->getSummonUnit()->getDefense()->getDefense());
        self::assertEquals($summonData['defense']['magic_defense'], $action->getSummonUnit()->getDefense()->getMagicDefense());
        self::assertEquals($summonData['defense']['block'], $action->getSummonUnit()->getDefense()->getBlock());
        self::assertEquals($summonData['defense']['magic_block'], $action->getSummonUnit()->getDefense()->getMagicBlock());
        self::assertEquals($summonData['defense']['mental_barrier'], $action->getSummonUnit()->getDefense()->getMentalBarrier());
    }

    /**
     * Тест на успешное создание BuffAction на основе массива с данными
     *
     * @throws Exception
     */
    public function testActionFactoryCreateBuffSuccess(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        // Полный набор данных
        $data = [
            'type'           => ActionInterface::BUFF,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_SELF,
            'name'           => $name = 'buff name test',
            'modify_method'  => $modifyMethod = 'modifyMethod',
            'power'          => $power = 150,
            'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
        ];

        $action = $this->getContainer()->getActionFactory()->create($data);

        self::assertInstanceOf(BuffAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_SELF, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($modifyMethod, $action->getModifyMethod());
        self::assertEquals(ActionInterface::SKIP_MESSAGE_METHOD, $action->getMessageMethod());

        // Вариант без message_method
        $data = [
            'type'           => ActionInterface::BUFF,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_SELF,
            'name'           => $name = 'buff name test',
            'modify_method'  => $modifyMethod = 'modifyMethod',
            'power'          => $power = 150,
        ];

        $action = $this->getContainer()->getActionFactory()->create($data);

        self::assertInstanceOf(BuffAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_SELF, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($modifyMethod, $action->getModifyMethod());
        self::assertEquals('buff', $action->getMessageMethod());
    }

    /**
     * Тест на успешное создание ResurrectionAction на основе массива с данными
     *
     * @throws Exception
     */
    public function testActionFactoryCreateResurrectionSuccess(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        // Полный набор данных
        $data = [
            'type'           => ActionInterface::RESURRECTION,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_DEAD_ALLIES,
            'name'           => $name = 'resurrection name test',
            'power'          => $power = 50,
            'icon'           => $icon = 'icon.png',
            'message_method' => $messageMethod = 'message method test',
        ];

        $action = $this->getContainer()->getActionFactory()->create($data);

        self::assertInstanceOf(ResurrectionAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_DEAD_ALLIES, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($icon, $action->getIcon());
        self::assertEquals($messageMethod, $action->getMessageMethod());

        // Минимальный вариант данных (может отсутствовать только icon)
        $data = [
            'type'           => ActionInterface::RESURRECTION,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_DEAD_ALLIES,
            'power'          => $power = 50,
            'name'           => ResurrectionAction::DEFAULT_NAME,
            'message_method' => $messageMethod = 'message method test',
        ];

        $action = $this->getContainer()->getActionFactory()->create($data);

        self::assertInstanceOf(ResurrectionAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_DEAD_ALLIES, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals(ResurrectionAction::DEFAULT_NAME, $action->getNameAction());
        self::assertEquals('', $action->getIcon());
        self::assertEquals($messageMethod, $action->getMessageMethod());
    }

    /**
     * Тест на успешное создание EffectAction на основе массива с данными
     *
     * @throws Exception
     */
    public function testActionFactoryCreateEffectSuccess(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $actionFactory = $this->getContainer()->getActionFactory();
        $effectFactory = $this->getContainer()->getEffectFactory();

        // Минимальный набор данных
        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_SELF,
            'name'           => $name = 'Effect test',
            'icon'           => $icon = 'icon.png',
            'effect'         => [
                'name'                  => 'Effect test #1',
                'icon'                  => 'effect_icon_#1',
                'duration'              => 10,
                'on_apply_actions'      => [
                    [
                        'type'           => ActionInterface::BUFF,
                        'action_unit'    => $unit,
                        'enemy_command'  => $enemyCommand,
                        'allies_command' => $command,
                        'type_target'    => ActionInterface::TARGET_SELF,
                        'name'           => 'use Reserve Forces',
                        'modify_method'  => 'multiplierMaxLife',
                        'power'          => 130,
                    ],
                ],
                'on_next_round_actions' => [],
                'on_disable_actions'    => [],
            ],
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(EffectAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_SELF, $action->getTypeTarget());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($icon, $action->getIcon());
        self::assertEquals($effectFactory->create($data['effect']), $action->getEffect());
        self::assertEquals(EffectAction::DEFAULT_ANIMATION_METHOD, $action->getAnimationMethod());
        self::assertEquals(EffectAction::DEFAULT_MESSAGE_METHOD, $action->getMessageMethod());

        // Полный набор данных
        $data = [
            'type'             => ActionInterface::EFFECT,
            'action_unit'      => $unit,
            'enemy_command'    => $enemyCommand,
            'allies_command'   => $command,
            'type_target'      => ActionInterface::TARGET_SELF,
            'name'             => $name = 'Effect test',
            'icon'             => $icon = 'icon.png',
            'effect'           => [
                'name'                  => 'Effect test #1',
                'icon'                  => 'effect_icon_#1',
                'duration'              => 10,
                'on_apply_actions'      => [
                    [
                        'type'           => ActionInterface::BUFF,
                        'action_unit'    => $unit,
                        'enemy_command'  => $enemyCommand,
                        'allies_command' => $command,
                        'type_target'    => ActionInterface::TARGET_SELF,
                        'name'           => 'use Reserve Forces',
                        'modify_method'  => 'multiplierMaxLife',
                        'power'          => 130,
                    ],
                ],
                'on_next_round_actions' => [],
                'on_disable_actions'    => [],
            ],
            'animation_method' => $animationMethod = 'custom_animation_method',
            'message_method'   => $messageMethod = 'custom_message_method',
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(EffectAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_SELF, $action->getTypeTarget());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($icon, $action->getIcon());
        self::assertEquals($effectFactory->create($data['effect']), $action->getEffect());
        self::assertEquals($animationMethod, $action->getAnimationMethod());
        self::assertEquals($messageMethod, $action->getMessageMethod());
    }

    /**
     * Тест на создание ManaRestoreAction из массива параметров
     *
     * @throws Exception
     */
    public function testActionFactoryCreateManaRestore(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $actionFactory = $this->getContainer()->getActionFactory();

        // Минимальный набор данных (без icon)
        $data = [
            'type'             => ActionInterface::MANA_RESTORE,
            'action_unit'      => $unit,
            'enemy_command'    => $enemyCommand,
            'allies_command'   => $command,
            'type_target'      => ActionInterface::TARGET_SELF,
            'power'            => $power = 50,
            'name'             => $name = 'mana restore',
            'animation_method' => $animationMethod = 'animation test',
            'message_method'   => $messageMethod = 'message test',
        ];

        $action = $actionFactory->create($data);

        self::assertInstanceOf(ManaRestoreAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_SELF, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($animationMethod, $action->getAnimationMethod());
        self::assertEquals($messageMethod, $action->getMessageMethod());
        self::assertEquals('', $action->getIcon());

        // Полный набор данных (с icon)
        $data = [
            'type'             => ActionInterface::MANA_RESTORE,
            'action_unit'      => $unit,
            'enemy_command'    => $enemyCommand,
            'allies_command'   => $command,
            'type_target'      => ActionInterface::TARGET_SELF,
            'power'            => $power = 50,
            'name'             => $name = 'mana restore',
            'animation_method' => $animationMethod = 'animation test',
            'message_method'   => $messageMethod = 'message test',
            'icon'             => $icon = 'icon test',
        ];

        $action = $actionFactory->create($data);

        self::assertEquals($icon, $action->getIcon());
    }

    /**
     * Тесты на различные варианты невалидных данных для (перебираются некорректные варианты для всех видов Action)
     *
     * @dataProvider failDataProvider
     * @param array $data
     * @param string $error
     * @throws Exception
     */
    public function testActionFactoryCreateFail(array $data, string $error): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($error);
        $this->getContainer()->getActionFactory()->create($data);
    }

    /**
     * Тест на ситуацию, когда внутри ActionFactory отсутствует нужный метод для создания объекта
     *
     * @throws Exception
     */
    public function testActionFactoryUnknownFactoryMethod(): void
    {
        $container = new Container();

        // Мы подменяем карту методов, передавая отсутствующий метод для создания DamageAction
        $methodMap = [
            ActionInterface::DAMAGE => 'createDamageActionUnknown',
        ];

        $actionFactory = new ActionFactory($container, $methodMap);
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2, $container);

        $offenseData = [
            'damage_type'         => 1,
            'weapon_type'         => WeaponTypeInterface::SWORD,
            'physical_damage'     => 35,
            'fire_damage'         => 0,
            'water_damage'        => 0,
            'air_damage'          => 0,
            'earth_damage'        => 0,
            'life_damage'         => 0,
            'death_damage'        => 0,
            'attack_speed'        => 1.2,
            'cast_speed'          => 0,
            'accuracy'            => 176,
            'magic_accuracy'      => 12,
            'block_ignoring'      => 0,
            'critical_chance'     => 5,
            'critical_multiplier' => 200,
            'damage_multiplier'   => 100,
            'vampirism'           => 0,
            'magic_vampirism'     => 0,
        ];

        $data = [
            'type'             => ActionInterface::DAMAGE,
            'action_unit'      => $unit,
            'enemy_command'    => $enemyCommand,
            'allies_command'   => $command,
            'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
            'offense'          => $offenseData,
            'can_be_avoided'   => $canBeAvoided = true,
            'name'             => $name = 'attack',
            'animation_method' => $animationMethod = 'animation test',
            'message_method'   => $messageMethod = 'message test',
        ];

        // Получаем Exception
        $this->expectExceptionMessage(ActionException::class);
        $this->expectExceptionMessage(ActionException::UNKNOWN_FACTORY_METHOD);
        $actionFactory->create($data);
    }

    /**
     * @return array
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function failDataProvider(): array
    {
        $actionUnit = TestUnitFactory::createByTemplate(1);
        $enemyUnit = TestUnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$actionUnit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        return [
            [
                // 0: Отсутствует type
                [
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'          => 50,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_TYPE_DATA,
            ],
            [
                // 1: type некорректного типа
                [
                    'type'           => 'ActionInterface::DAMAGE',
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'        => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                    ],
                    'can_be_avoided' => true,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_TYPE_DATA,
            ],
            [
                // 2: type несуществующего типа Action
                [
                    'type'           => 33,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'          => 50,
                    'name'           => 'action name',
                ],
                ActionException::UNKNOWN_TYPE_ACTION,
            ],
            [
                // 3: Отсутствует action_unit
                [
                    'type'             => ActionInterface::DAMAGE,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'          => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                    ],
                    'can_be_avoided'   => true,
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_ACTION_UNIT_DATA,
            ],
            [
                // 4: action_unit содержит некорректный объект
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => new UnitFactory(),
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'          => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                    ],
                    'can_be_avoided'   => true,
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_ACTION_UNIT_DATA,
            ],
            [
                // 5: Отсутствует enemy_command
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $actionUnit,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'          => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                    ],
                    'can_be_avoided'   => true,
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_COMMAND_DATA,
            ],
            [
                // 6: enemy_command содержит некорректный объект
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => new UnitFactory(),
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'          => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                    ],
                    'can_be_avoided'   => true,
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_COMMAND_DATA,
            ],
            [
                // 7: Отсутствует allies_command
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'          => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                    ],
                    'can_be_avoided'   => true,
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_COMMAND_DATA,
            ],
            [
                // 8: allies_command содержит некорректный объект
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => new UnitFactory(),
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'          => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                    ],
                    'can_be_avoided'   => true,
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_COMMAND_DATA,
            ],
            [
                // 9: Отсутствует type_target [для DamageAction]
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'offense'          => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                    ],
                    'can_be_avoided'   => true,
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // 10: type_target некорректного типа [для DamageAction]
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => true,
                    'offense'          => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                    ],
                    'can_be_avoided'   => true,
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // 11: Отсутствует can_be_avoided [для DamageAction]
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'          => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                    ],
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_CAN_BE_AVOIDED,
            ],
            [
                // 12: can_be_avoided некорректного типа [для DamageAction]
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'          => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                    ],
                    'can_be_avoided'   => '0',
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_CAN_BE_AVOIDED,
            ],
            [
                // 13: Отсутствует name [для DamageAction]
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'          => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                    ],
                    'can_be_avoided'   => true,
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 14: name null [для DamageAction]
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'          => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                    ],
                    'can_be_avoided'   => true,
                    'name'             => null,
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 15: name некорректного типа [для DamageAction]
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'          => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                    ],
                    'can_be_avoided'   => true,
                    'name'             => ['action name'],
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 16: Отсутствует animation_method [для DamageAction]
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'        => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                    ],
                    'can_be_avoided' => true,
                    'name'           => 'action name',
                    'message_method' => 'message test',
                ],
                ActionException::INVALID_ANIMATION_METHOD_DATA,
            ],
            [
                // 17: animation_method некорректного типа [для DamageAction]
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'          => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                    ],
                    'can_be_avoided'   => true,
                    'name'             => 'action name',
                    'animation_method' => null,
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_ANIMATION_METHOD_DATA,
            ],
            [
                // 18: Отсутствует message_method [для DamageAction]
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'          => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                    ],
                    'can_be_avoided'   => true,
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                ],
                ActionException::INVALID_MESSAGE_METHOD_DATA,
            ],
            [
                // 19: message_method некорректного типа [для DamageAction]
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'          => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                    ],
                    'can_be_avoided'   => true,
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                    'message_method'   => null,
                ],
                ActionException::INVALID_MESSAGE_METHOD_DATA,
            ],
            [
                // 23: Отсутствует name [для SummonAction]
                [
                    'type'           => ActionInterface::SUMMON,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'icon'           => '/images/icons/ability/198.png',
                    'summon'         => [
                        'name'                         => 'Fire Elemental',
                        'level'                        => 3,
                        'avatar'                       => '/images/avas/summon/fire-elemental.png',
                        'life'                         => 62,
                        'total_life'                   => 62,
                        'mana'                         => 17,
                        'total_mana'                   => 17,
                        'melee'                        => true,
                        'class'                        => null,
                        'race'                         => 10,
                        'add_concentration_multiplier' => 0,
                        'add_rage_multiplier'          => 0,
                        'offense'                      => [
                            'damage_type'         => 2,
                            'weapon_type'         => WeaponTypeInterface::UNARMED,
                            'physical_damage'     => 0,
                            'fire_damage'         => 17,
                            'water_damage'        => 0,
                            'air_damage'          => 0,
                            'earth_damage'        => 0,
                            'life_damage'         => 0,
                            'death_damage'        => 0,
                            'attack_speed'        => 0,
                            'cast_speed'          => 1.1,
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
                        ],
                    ],
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 24: name некорректного типа [для SummonAction]
                [
                    'type'           => ActionInterface::SUMMON,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'name'           => 123,
                    'icon'           => '/images/icons/ability/198.png',
                    'summon'         => [
                        'name'                         => 'Fire Elemental',
                        'level'                        => 3,
                        'avatar'                       => '/images/avas/summon/fire-elemental.png',
                        'life'                         => 62,
                        'total_life'                   => 62,
                        'mana'                         => 17,
                        'total_mana'                   => 17,
                        'melee'                        => true,
                        'class'                        => null,
                        'race'                         => 10,
                        'add_concentration_multiplier' => 0,
                        'add_rage_multiplier'          => 0,
                        'offense'                      => [
                            'damage_type'         => 2,
                            'weapon_type'         => WeaponTypeInterface::UNARMED,
                            'physical_damage'     => 0,
                            'fire_damage'         => 17,
                            'water_damage'        => 0,
                            'air_damage'          => 0,
                            'earth_damage'        => 0,
                            'life_damage'         => 0,
                            'death_damage'        => 0,
                            'attack_speed'        => 0,
                            'cast_speed'          => 1.1,
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
                        ],
                    ],
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 25: Отсутствует summon [для SummonAction]
                [
                    'type'           => ActionInterface::SUMMON,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'name'           => 'summon test',
                ],
                ActionException::INVALID_SUMMON_DATA,
            ],
            [
                // 26: summon некорректного типа [для SummonAction]
                [
                    'type'           => ActionInterface::SUMMON,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'name'           => 'summon test',
                    'summon'         => 'summon data',
                ],
                ActionException::INVALID_SUMMON_DATA,
            ],
            [
                // 27: summon не содержит нужных параметров [для SummonAction]. Для данного теста достаточно одной проверки,
                // так как все варианты невалидных данных по юниту проверяются уже в UnitFactory
                [
                    'type'           => ActionInterface::SUMMON,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'name'           => 'summon test',
                    'summon'         => [],
                ],
                UnitException::INCORRECT_NAME,
            ],
            [
                // 28: Отсутствует type_target [для BuffAction]
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'name'           => 'buff test',
                    'modify_method'  => 'modify method test',
                    'power'          => 150,
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // 29: type_target некорректного типа [для BuffAction]
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => true,
                    'name'           => 'buff test',
                    'modify_method'  => 'modify method test',
                    'power'          => 150,
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // 30: Отсутствует name [для BuffAction]
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'modify_method'  => 'modify method test',
                    'power'          => 150,
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 31: name некорректного типа [для BuffAction]
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 123,
                    'modify_method'  => 'modify method test',
                    'power'          => 150,
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 32: Отсутствует modify_method [для BuffAction]
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'buff test',
                    'power'          => 150,
                ],
                ActionException::INVALID_MODIFY_METHOD_DATA,
            ],
            [
                // 33: modify_method некорректного типа [для BuffAction]
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'buff test',
                    'modify_method'  => ['modify method test'],
                    'power'          => 150,
                ],
                ActionException::INVALID_MODIFY_METHOD_DATA,
            ],
            [
                // 34: Отсутствует power [для BuffAction]
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'buff test',
                    'modify_method'  => 'modify method test',
                ],
                ActionException::INVALID_POWER_DATA,
            ],
            [
                // 35: power некорректного типа [для BuffAction]
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'buff test',
                    'modify_method'  => 'modify method test',
                    'power'          => '150',
                ],
                ActionException::INVALID_POWER_DATA,
            ],
            [
                // 36: message_method некорректного типа [для BuffAction]
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'buff test',
                    'modify_method'  => 'modify method test',
                    'power'          => 150,
                    'message_method' => 123,
                ],
                ActionException::INVALID_MESSAGE_METHOD_DATA,
            ],
            // EffectAction
            [
                // 37: Отсутствует type_target
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'name'           => 'Effect test',
                    'effect'         => [
                        [
                            'name'                  => 'Effect test #1',
                            'icon'                  => 'effect_icon_#1',
                            'duration'              => 10,
                            'on_apply_actions'      => [
                                [
                                    'type'           => ActionInterface::BUFF,
                                    'action_unit'    => $actionUnit,
                                    'enemy_command'  => $enemyCommand,
                                    'allies_command' => $command,
                                    'type_target'    => ActionInterface::TARGET_SELF,
                                    'name'           => 'use Reserve Forces',
                                    'modify_method'  => 'multiplierMaxLife',
                                    'power'          => 130,
                                ],
                            ],
                            'on_next_round_actions' => [],
                            'on_disable_actions'    => [],
                        ],
                    ],
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // 38: type_target некорректного типа
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => 'self',
                    'name'           => 'Effect test',
                    'effect'         => [
                        [
                            'name'                  => 'Effect test #1',
                            'icon'                  => 'effect_icon_#1',
                            'duration'              => 10,
                            'on_apply_actions'      => [
                                [
                                    'type'           => ActionInterface::BUFF,
                                    'action_unit'    => $actionUnit,
                                    'enemy_command'  => $enemyCommand,
                                    'allies_command' => $command,
                                    'type_target'    => ActionInterface::TARGET_SELF,
                                    'name'           => 'use Reserve Forces',
                                    'modify_method'  => 'multiplierMaxLife',
                                    'power'          => 130,
                                ],
                            ],
                            'on_next_round_actions' => [],
                            'on_disable_actions'    => [],
                        ],
                    ],
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // 39: Отсутствует name
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'effect'         => [
                        [
                            'name'                  => 'Effect test #1',
                            'icon'                  => 'effect_icon_#1',
                            'duration'              => 10,
                            'on_apply_actions'      => [
                                [
                                    'type'           => ActionInterface::BUFF,
                                    'action_unit'    => $actionUnit,
                                    'enemy_command'  => $enemyCommand,
                                    'allies_command' => $command,
                                    'type_target'    => ActionInterface::TARGET_SELF,
                                    'name'           => 'use Reserve Forces',
                                    'modify_method'  => 'multiplierMaxLife',
                                    'power'          => 130,
                                ],
                            ],
                            'on_next_round_actions' => [],
                            'on_disable_actions'    => [],
                        ],
                    ],
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 40: name некорректного типа
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => true,
                    'effect'         => [
                        [
                            'name'                  => 'Effect test #1',
                            'icon'                  => 'effect_icon_#1',
                            'duration'              => 10,
                            'on_apply_actions'      => [
                                [
                                    'type'           => ActionInterface::BUFF,
                                    'action_unit'    => $actionUnit,
                                    'enemy_command'  => $enemyCommand,
                                    'allies_command' => $command,
                                    'type_target'    => ActionInterface::TARGET_SELF,
                                    'name'           => 'use Reserve Forces',
                                    'modify_method'  => 'multiplierMaxLife',
                                    'power'          => 130,
                                ],
                            ],
                            'on_next_round_actions' => [],
                            'on_disable_actions'    => [],
                        ],
                    ],
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 41: Отсутствует effect
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'Effect test',
                ],
                ActionException::INVALID_EFFECT_DATA,
            ],
            [
                // 42: effect некорректного типа
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'Effect test',
                    'effect'         => 'effect',
                ],
                ActionException::INVALID_EFFECT_DATA,
            ],
            [
                // 43: ResurrectionAction - отсутствует type_target
                [
                    'type'           => ActionInterface::RESURRECTION,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'name'           => 'resurrection name test',
                    'power'          => 50,
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // 44: ResurrectionAction - type_target некорректного типа
                [
                    'type'           => ActionInterface::RESURRECTION,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => 'self',
                    'name'           => 'resurrection name test',
                    'power'          => 50,
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // 45: ResurrectionAction - отсутствует power
                [
                    'type'           => ActionInterface::RESURRECTION,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_DEAD_ALLIES,
                    'name'           => 'resurrection name test',
                ],
                ActionException::INVALID_POWER_DATA,
            ],
            [
                // 46: ResurrectionAction - power некорректного типа
                [
                    'type'           => ActionInterface::RESURRECTION,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_DEAD_ALLIES,
                    'name'           => 'resurrection name test',
                    'power'          => true,
                ],
                ActionException::INVALID_POWER_DATA,
            ],
            [
                // 47: ResurrectionAction - отсутствует name
                [
                    'type'           => ActionInterface::RESURRECTION,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_DEAD_ALLIES,
                    'power'          => 50,
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 48: ResurrectionAction - name некорректного типа
                [
                    'type'           => ActionInterface::RESURRECTION,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_DEAD_ALLIES,
                    'name'           => 100,
                    'power'          => 50,
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 49: ResurrectionAction - отсутствует message_method
                [
                    'type'           => ActionInterface::RESURRECTION,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_DEAD_ALLIES,
                    'name'           => 'name',
                    'power'          => 50,
                ],
                ActionException::INVALID_MESSAGE_METHOD_DATA,
            ],
            [
                // 50: ResurrectionAction - message_method некорректного типа
                [
                    'type'           => ActionInterface::RESURRECTION,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_DEAD_ALLIES,
                    'name'           => 'name',
                    'power'          => 50,
                    'message_method' => true,
                ],
                ActionException::INVALID_MESSAGE_METHOD_DATA,
            ],
            [
                // 51: DamageAction - некорректный icon
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'icon'             => 123,
                    'offense'          => [
                        'damage_type'         => 1,
                        'weapon_type'         => WeaponTypeInterface::SWORD,
                        'physical_damage'     => 10,
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
                        'critical_multiplier' => 200,
                        'damage_multiplier'   => 100,
                        'vampirism'           => 0,
                        'magic_vampirism'     => 0,
                    ],
                    'defense'          => [
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
                    ],
                    'can_be_avoided'   => true,
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_ICON_DATA,
            ],
            [
                // 52: EffectAction - некорректный icon
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'Effect',
                    'icon'           => true,
                    'effect'         => [
                        'name'                  => 'Effect test #1',
                        'icon'                  => 'effect_icon_#1',
                        'duration'              => 10,
                        'on_apply_actions'      => [
                            [
                                'type'           => ActionInterface::BUFF,
                                'action_unit'    => $actionUnit,
                                'enemy_command'  => $enemyCommand,
                                'allies_command' => $command,
                                'type_target'    => ActionInterface::TARGET_SELF,
                                'name'           => 'use Reserve Forces',
                                'modify_method'  => 'multiplierMaxLife',
                                'power'          => 130,
                            ],
                        ],
                        'on_next_round_actions' => [],
                        'on_disable_actions'    => [],
                    ],
                ],
                ActionException::INVALID_ICON_DATA,
            ],
            [
                // 53: EffectAction - некорректный animation_method
                [
                    'type'             => ActionInterface::EFFECT,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'name'             => 'Effect',
                    'icon'             => 'icon.png',
                    'effect'           => [
                        'name'                  => 'Effect test #1',
                        'icon'                  => 'effect_icon_#1',
                        'duration'              => 10,
                        'on_apply_actions'      => [
                            [
                                'type'           => ActionInterface::BUFF,
                                'action_unit'    => $actionUnit,
                                'enemy_command'  => $enemyCommand,
                                'allies_command' => $command,
                                'type_target'    => ActionInterface::TARGET_SELF,
                                'name'           => 'use Reserve Forces',
                                'modify_method'  => 'multiplierMaxLife',
                                'power'          => 130,
                            ],
                        ],
                        'on_next_round_actions' => [],
                        'on_disable_actions'    => [],
                    ],
                    'animation_method' => 123,
                ],
                ActionException::INVALID_ANIMATION_METHOD_DATA,
            ],
            [
                // 54: EffectAction - некорректный message_method
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'Effect',
                    'icon'           => 'icon.png',
                    'effect'         => [
                        'name'                  => 'Effect test #1',
                        'icon'                  => 'effect_icon_#1',
                        'duration'              => 10,
                        'on_apply_actions'      => [
                            [
                                'type'           => ActionInterface::BUFF,
                                'action_unit'    => $actionUnit,
                                'enemy_command'  => $enemyCommand,
                                'allies_command' => $command,
                                'type_target'    => ActionInterface::TARGET_SELF,
                                'name'           => 'use Reserve Forces',
                                'modify_method'  => 'multiplierMaxLife',
                                'power'          => 130,
                            ],
                        ],
                        'on_next_round_actions' => [],
                        'on_disable_actions'    => [],
                    ],
                    'message_method' => true,
                ],
                ActionException::INVALID_MESSAGE_METHOD_DATA,
            ],

            // offense и multiple_offense для DamageAction. Проверяем наличие параметра и что он null или array.
            // Остальная валидация происходит в OffenseFactory
            [
                // 55: offense некорректного типа
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'          => 60,
                    'can_be_avoided'   => true,
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                    'message_method'   => 'test',
                ],
                ActionException::INVALID_OFFENSE_DATA,
            ],
            [
                // 56: multiple_offense некорректного типа
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'multiple_offense' => '',
                    'can_be_avoided'   => true,
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                    'message_method'   => 'test',
                ],
                ActionException::INVALID_MULTIPLE_OFFENSE_DATA,
            ],

            // ManaRestoreAction
            [
                // 57. Отсутствует type_target
                [
                    'type'             => ActionInterface::MANA_RESTORE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'power'            => 50,
                    'name'             => 'mana restore',
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                    'icon'             => 'icon test',
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // 58. type_target некорректного типа
                [
                    'type'             => ActionInterface::MANA_RESTORE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => 'target',
                    'power'            => 50,
                    'name'             => 'mana restore',
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                    'icon'             => 'icon test',
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // 59. Отсутствует power
                [
                    'type'             => ActionInterface::MANA_RESTORE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'name'             => 'mana restore',
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                    'icon'             => 'icon test',
                ],
                ActionException::INVALID_POWER_DATA,
            ],
            [
                // 60. power некорректного типа
                [
                    'type'             => ActionInterface::MANA_RESTORE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'power'            => '50',
                    'name'             => 'mana restore',
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                    'icon'             => 'icon test',
                ],
                ActionException::INVALID_POWER_DATA,
            ],
            [
                // 61. Отсутствует name
                [
                    'type'             => ActionInterface::MANA_RESTORE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'power'            => 50,
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                    'icon'             => 'icon test',
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 62. name некорректного типа
                [
                    'type'             => ActionInterface::MANA_RESTORE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'power'            => 50,
                    'name'             => null,
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                    'icon'             => 'icon test',
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 63. Отсутствует animation_method
                [
                    'type'           => ActionInterface::MANA_RESTORE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'power'          => 50,
                    'name'           => 'mana restore',
                    'message_method' => 'message test',
                    'icon'           => 'icon test',
                ],
                ActionException::INVALID_ANIMATION_METHOD_DATA,
            ],
            [
                // 64. animation_method некорректного типа
                [
                    'type'             => ActionInterface::MANA_RESTORE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'power'            => 50,
                    'name'             => 'mana restore',
                    'animation_method' => true,
                    'message_method'   => 'message test',
                    'icon'             => 'icon test',
                ],
                ActionException::INVALID_ANIMATION_METHOD_DATA,
            ],
            [
                // 65. Отсутствует message_method
                [
                    'type'             => ActionInterface::MANA_RESTORE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'power'            => 50,
                    'name'             => 'mana restore',
                    'animation_method' => 'animation test',
                    'icon'             => 'icon test',
                ],
                ActionException::INVALID_MESSAGE_METHOD_DATA,
            ],
            [
                // 66. message_method некорректного типа
                [
                    'type'             => ActionInterface::MANA_RESTORE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'power'            => 50,
                    'name'             => 'mana restore',
                    'animation_method' => 'animation test',
                    'message_method'   => 123,
                    'icon'             => 'icon test',
                ],
                ActionException::INVALID_MESSAGE_METHOD_DATA,
            ],
            [
                // 67. icon некорректного типа
                [
                    'type'             => ActionInterface::MANA_RESTORE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'power'            => 50,
                    'name'             => 'mana restore',
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                    'icon'             => ['icon'],
                ],
                ActionException::INVALID_ICON_DATA,
            ],

            // HealAction
            [
                // 20: Отсутствует type_target [для HealAction]
                [
                    'type'             => ActionInterface::HEAL,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'power'            => 50,
                    'name'             => 'action name',
                    'animation_method' => 'animation',
                    'message_method'   => 'message',
                    'icon'             => 'icon.png',
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // 21: type_target некорректного типа [для HealAction]
                [
                    'type'             => ActionInterface::HEAL,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => true,
                    'power'            => 50,
                    'name'             => 'action name',
                    'animation_method' => 'animation',
                    'message_method'   => 'message',
                    'icon'             => 'icon.png',
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // 58: Отсутствует power для HealAction
                [
                    'type'             => ActionInterface::HEAL,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_WOUNDED_ALLIES,
                    'animation_method' => 'animation',
                    'message_method'   => 'message',
                    'icon'             => 'icon.png',
                ],
                ActionException::INVALID_POWER_DATA,
            ],
            [
                // 58: Некорректный power для HealAction
                [
                    'type'             => ActionInterface::HEAL,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_WOUNDED_ALLIES,
                    'power'            => true,
                    'animation_method' => 'animation',
                    'message_method'   => 'message',
                    'icon'             => 'icon.png',
                ],
                ActionException::INVALID_POWER_DATA,
            ],
            [
                // 22: Отсутствует name [для HealAction]
                [
                    'type'             => ActionInterface::HEAL,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'            => 50,
                    'can_be_avoided'   => true,
                    'animation_method' => 'animation',
                    'message_method'   => 'message',
                    'icon'             => 'icon.png',
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 22: name некорректного типа [для HealAction]
                [
                    'type'             => ActionInterface::HEAL,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'            => 50,
                    'can_be_avoided'   => true,
                    'name'             => 123,
                    'animation_method' => 'animation',
                    'message_method'   => 'message',
                    'icon'             => 'icon.png',
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 22: Отсутствует animation_method [для HealAction]
                [
                    'type'           => ActionInterface::HEAL,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'          => 50,
                    'can_be_avoided' => true,
                    'name'           => '',
                    'message_method' => 'message',
                    'icon'           => 'icon.png',
                ],
                ActionException::INVALID_ANIMATION_METHOD_DATA,
            ],
            [
                // 22: animation_method некорректного типа [для HealAction]
                [
                    'type'             => ActionInterface::HEAL,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'            => 50,
                    'can_be_avoided'   => true,
                    'name'             => '',
                    'animation_method' => null,
                    'message_method'   => 'message',
                    'icon'             => 'icon.png',
                ],
                ActionException::INVALID_ANIMATION_METHOD_DATA,
            ],
            [
                // 22: Отсутствует message_method [для HealAction]
                [
                    'type'             => ActionInterface::HEAL,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'            => 50,
                    'can_be_avoided'   => true,
                    'name'             => '',
                    'animation_method' => '',
                    'icon'             => 'icon.png',
                ],
                ActionException::INVALID_MESSAGE_METHOD_DATA,
            ],
            [
                // 22: message_method некорректного типа [для HealAction]
                [
                    'type'             => ActionInterface::HEAL,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'            => 50,
                    'can_be_avoided'   => true,
                    'name'             => '',
                    'animation_method' => '',
                    'message_method'   => false,
                    'icon'             => 'icon.png',
                ],
                ActionException::INVALID_MESSAGE_METHOD_DATA,
            ],
            [
                // 22: icon некорректного типа [для HealAction]
                [
                    'type'             => ActionInterface::HEAL,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'            => 50,
                    'can_be_avoided'   => true,
                    'name'             => '',
                    'animation_method' => '',
                    'message_method'   => '',
                    'icon'             => 11.11,
                ],
                ActionException::INVALID_ICON_DATA,
            ],
        ];
    }
}
