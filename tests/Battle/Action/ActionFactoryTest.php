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
use Battle\Action\ParalysisAction;
use Battle\Action\ResurrectionAction;
use Battle\Action\SummonAction;
use Battle\Action\WaitAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Unit\Effect\EffectFactory;
use Battle\Unit\Offense\OffenseFactory;
use Battle\Unit\UnitException;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\BaseFactory;
use Tests\Battle\Factory\UnitFactory as TestUnitFactory;
use Tests\Battle\Factory\UnitFactoryException;
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
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

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
            'accuracy'            => 176,
            'magic_accuracy'      => 12,
            'block_ignore'        => 0,
            'critical_chance'     => 5,
            'critical_multiplier' => 200,
            'vampire'             => 0,
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

        $action = $this->getActionFactory()->create($data);

        self::assertInstanceOf(DamageAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_RANDOM_ENEMY, $action->getTypeTarget());
        self::assertEquals(OffenseFactory::create($offenseData), $action->getOffense());
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

        $action = $this->getActionFactory()->create($data);

        self::assertInstanceOf(DamageAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_RANDOM_ENEMY, $action->getTypeTarget());
        self::assertEquals(OffenseFactory::create($offenseData), $action->getOffense());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($animationMethod, $action->getAnimationMethod());
        self::assertEquals($messageMethod, $action->getMessageMethod());
        self::assertEquals($icon, $action->getIcon());
        self::assertEquals($canBeAvoided, $action->isCanBeAvoided());
    }

    /**
     * Тест на успешное создание HealAction на основе массива с данными
     *
     * @throws Exception
     */
    public function testActionFactoryCreateHealSuccess(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        // Вариант с минимальным набором данных
        $data = [
            'type'           => ActionInterface::HEAL,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_WOUNDED_ALLIES,
            'power'          => $power = 123,
        ];

        $action = $this->getActionFactory()->create($data);

        self::assertInstanceOf(HealAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_WOUNDED_ALLIES, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals('heal', $action->getNameAction());
        self::assertEquals('', $action->getIcon());

        // Полный набор данных
        $data = [
            'type'             => ActionInterface::HEAL,
            'action_unit'      => $unit,
            'enemy_command'    => $enemyCommand,
            'allies_command'   => $command,
            'type_target'      => ActionInterface::TARGET_WOUNDED_ALLIES,
            'power'            => $power = 50,
            'name'             => $name = 'action name 123',
            'animation_method' => $animationMethod = 'effectHeal',
            'icon'             => $icon = 'icon.png',
        ];

        $action = $this->getActionFactory()->create($data);

        self::assertInstanceOf(HealAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_WOUNDED_ALLIES, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($animationMethod, $action->getAnimationMethod());
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

        $action = $this->getActionFactory()->create($data);

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
        ];

        $action = $this->getActionFactory()->create($data);

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
            'name'         => 'Imp',
            'level'        => 1,
            'avatar'       => '/images/avas/monsters/004.png',
            'block_ignore' => 0,
            'life'         => 30,
            'total_life'   => 30,
            'mana'         => 0,
            'total_mana'   => 0,
            'melee'        => true,
            'class'        => 1,
            'race'         => 9,
            'offense'      => [
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
                'accuracy'            => 200,
                'magic_accuracy'      => 100,
                'block_ignore'        => 0,
                'critical_chance'     => 5,
                'critical_multiplier' => 150,
                'vampire'             => 0,
            ],
            'defense'      => [
                'physical_resist' => 0,
                'defense'         => 100,
                'magic_defense'   => 50,
                'block'           => 0,
                'magic_block'     => 0,
                'mental_barrier'  => 0,
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

        $action = $this->getActionFactory()->create($data);

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
        self::assertEquals($summonData['offense']['accuracy'], $action->getSummonUnit()->getOffense()->getAccuracy());
        self::assertEquals($summonData['offense']['magic_accuracy'], $action->getSummonUnit()->getOffense()->getMagicAccuracy());
        self::assertEquals($summonData['offense']['block_ignore'], $action->getSummonUnit()->getOffense()->getBlockIgnore());

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

        $action = $this->getActionFactory()->create($data);

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

        $action = $this->getActionFactory()->create($data);

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

        $action = $this->getActionFactory()->create($data);

        self::assertInstanceOf(ResurrectionAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_DEAD_ALLIES, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals($name, $action->getNameAction());
        self::assertEquals($icon, $action->getIcon());
        self::assertEquals($messageMethod, $action->getMessageMethod());

        // Минимальный вариант данных
        $data = [
            'type'           => ActionInterface::RESURRECTION,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_DEAD_ALLIES,
            'power'          => $power = 50,
        ];

        $action = $this->getActionFactory()->create($data);

        self::assertInstanceOf(ResurrectionAction::class, $action);
        self::assertEquals($unit, $action->getActionUnit());
        self::assertEquals(ActionInterface::TARGET_DEAD_ALLIES, $action->getTypeTarget());
        self::assertEquals($power, $action->getPower());
        self::assertEquals('resurrected', $action->getNameAction());
        self::assertEquals('', $action->getIcon());
    }

    /**
     * Тест на успешное создание EffectAction на основе массива с данными
     *
     * @throws Exception
     */
    public function testActionFactoryCreateEffectSuccess(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $actionFactory = $this->getActionFactory();
        $effectFactory = new EffectFactory($actionFactory);

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
        $this->getActionFactory()->create($data);
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 150,
                        'vampire'             => 0,
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 150,
                        'vampire'             => 0,
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 150,
                        'vampire'             => 0,
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 150,
                        'vampire'             => 0,
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 150,
                        'vampire'             => 0,
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 150,
                        'vampire'             => 0,
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 150,
                        'vampire'             => 0,
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 150,
                        'vampire'             => 0,
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 150,
                        'vampire'             => 0,
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 150,
                        'vampire'             => 0,
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 150,
                        'vampire'             => 0,
                    ],
                    'can_be_avoided'   => '0',
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_CAN_BE_AVOIDED,
            ],
            [
                // 14: Отсутствует name [для DamageAction]
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 150,
                        'vampire'             => 0,
                    ],
                    'can_be_avoided'   => true,
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 15: name null [для DamageAction]
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 150,
                        'vampire'             => 0,
                    ],
                    'can_be_avoided'   => true,
                    'name'             => null,
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 16: name некорректного типа [для DamageAction]
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 150,
                        'vampire'             => 0,
                    ],
                    'can_be_avoided'   => true,
                    'name'             => ['action name'],
                    'animation_method' => 'animation test',
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 17: Отсутствует animation_method [для DamageAction]
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 150,
                        'vampire'             => 0,
                    ],
                    'can_be_avoided' => true,
                    'name'           => 'action name',
                    'message_method' => 'message test',
                ],
                ActionException::INVALID_ANIMATION_DATA,
            ],
            [
                // 18: animation_method некорректного типа [для DamageAction]
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 150,
                        'vampire'             => 0,
                    ],
                    'can_be_avoided'   => true,
                    'name'             => 'action name',
                    'animation_method' => null,
                    'message_method'   => 'message test',
                ],
                ActionException::INVALID_ANIMATION_DATA,
            ],
            [
                // 19: Отсутствует message_method [для DamageAction]
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 150,
                        'vampire'             => 0,
                    ],
                    'can_be_avoided'   => true,
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                ],
                ActionException::INVALID_MESSAGE_METHOD,
            ],
            [
                // 20: message_method некорректного типа [для DamageAction]
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 150,
                        'vampire'             => 0,
                    ],
                    'can_be_avoided'   => true,
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                    'message_method'   => null,
                ],
                ActionException::INVALID_MESSAGE_METHOD,
            ],
            [
                // 21: Отсутствует type_target [для HealAction]
                [
                    'type'           => ActionInterface::HEAL,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'power'          => 50,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // 22: type_target некорректного типа [для HealAction]
                [
                    'type'           => ActionInterface::HEAL,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => true,
                    'power'          => 50,
                    'name'           => 'action name',
                ],
                ActionException::INVALID_TYPE_TARGET_DATA,
            ],
            [
                // 23: name некорректного типа [для HealAction]
                [
                    'type'           => ActionInterface::HEAL,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'power'          => 50,
                    'can_be_avoided' => true,
                    'name'           => 123,
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 24: Отсутствует name [для SummonAction]
                [
                    'type'           => ActionInterface::SUMMON,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 25: name некорректного типа [для SummonAction]
                [
                    'type'           => ActionInterface::SUMMON,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'name'           => [],
                ],
                ActionException::INVALID_NAME_DATA,
            ],
            [
                // 26: Отсутствует summon [для SummonAction]
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
                // 27: summon некорректного типа [для SummonAction]
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
                // 28: summon не содержит нужных параметров [для SummonAction]. Для данного теста достаточно одной проверки,
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
                // 29: Отсутствует type_target [для BuffAction]
                [
                    'type'           => ActionInterface::HEAL,
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
                // 30: type_target некорректного типа [для BuffAction]
                [
                    'type'           => ActionInterface::HEAL,
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
                // 31: Отсутствует name [для BuffAction]
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
                // 32: name некорректного типа [для BuffAction]
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
                // 33: Отсутствует modify_method [для BuffAction]
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
                // 34: modify_method некорректного типа [для BuffAction]
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
                // 35: Отсутствует power [для BuffAction]
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
                // 36: power некорректного типа [для BuffAction]
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
                // 37: message_method некорректного типа [для BuffAction]
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
                ActionException::INVALID_MESSAGE_METHOD,
            ],
            // EffectAction
            // 38: Отсутствует type_target
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'name'           => 'Effect test',
                    'effects'        => [
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
            // 39: type_target некорректного типа
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => 'self',
                    'name'           => 'Effect test',
                    'effects'        => [
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
            // 40: Отсутствует name
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'effects'        => [
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
            // 41: name некорректного типа
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => true,
                    'effects'        => [
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
            // 42: Отсутствует effect
            [
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
            // 43: effects некорректного типа
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'Effect test',
                    'effect'         => 'effects',
                ],
                ActionException::INVALID_EFFECT_DATA,
            ],
            // 44: effects вместо массива данных по эффекту содержит строку
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'Effect test',
                    'effects'        => [
                        'buff effect',
                    ],
                ],
                ActionException::INVALID_EFFECT_DATA,
            ],
            // 45: ResurrectionAction - отсутствует type_target
            [
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
            // 46: ResurrectionAction - type_target некорректного типа
            [
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
            // 47: ResurrectionAction - отсутствует power
            [
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
            // 48: ResurrectionAction - power некорректного типа
            [
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
            // 49: ResurrectionAction - name некорректного типа
            [
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
            // 50: ResurrectionAction - message_method некорректного типа
            [
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
                ActionException::INVALID_MESSAGE_METHOD,
            ],
            // 51: DamageAction - некорректный icon
            [
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
                    'icon'           => 123,
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
                        'accuracy'            => 200,
                        'magic_accuracy'      => 100,
                        'block_ignore'        => 0,
                        'critical_chance'     => 5,
                        'critical_multiplier' => 200,
                        'vampire'             => 0,
                    ],
                    'can_be_avoided' => true,
                ],
                ActionException::INVALID_ICON,
            ],
            // 52: EffectAction - некорректный icon
            [
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
                ActionException::INVALID_ICON,
            ],
            // 53: EffectAction - некорректный animation_method
            [
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
                ActionException::INVALID_ANIMATION_DATA,
            ],
            // 54: EffectAction - некорректный message_method
            [
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
                ActionException::INVALID_MESSAGE_METHOD,
            ],
            [
                // 55: Некорректный power для HealAction
                [
                    'type'           => ActionInterface::HEAL,
                    'action_unit'    => $actionUnit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_WOUNDED_ALLIES,
                    'power'          => true,
                ],
                ActionException::INVALID_POWER_DATA,
            ],

            // offense для DamageAction. Проверяем наличие параметра и что он массив. Остальная валидация происходит в OffenseFactory

            // 55: Отсутствует offense
            [
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $actionUnit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'can_be_avoided'   => true,
                    'name'             => 'action name',
                    'animation_method' => 'animation test',
                    'message_method'   => 'test',
                ],
                ActionException::INVALID_OFFENSE_DATA,
            ],

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
        ];
    }

    private function getActionFactory(): ActionFactory
    {
        return $this->getContainer()->getActionFactory();
    }
}
