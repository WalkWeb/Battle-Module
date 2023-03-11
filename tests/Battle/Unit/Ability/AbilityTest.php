<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability;

use Battle\Action\ActionInterface;
use Battle\Command\CommandFactory;
use Battle\Container\Container;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\AbilityException;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\UnitInterface;
use Battle\Weapon\Type\WeaponTypeInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

/**
 * Способностей много, и создавать тесты на все варианты в одном классе неразумно. По этому тесты под способности
 * пишутся в тестах с аналогичным типом/названием.
 *
 * В данном классе лишь небольшое количество тестов на самые базовые проверки
 *
 * @package Tests\Battle\Unit\Ability
 */
class AbilityTest extends AbstractUnitTest
{
    /**
     * Тест на проверку базовых параметров способностей: name, icon, unit, disposable
     *
     * @throws Exception
     */
    public function testAbilityCreate(): void
    {
        $name = 'Heavy Strike';
        $icon = '/images/icons/ability/335.png';
        $disposable = false;
        $chanceActivate = 50;
        $allowedWeaponType = [
            WeaponTypeInterface::SWORD,
            WeaponTypeInterface::AXE,
            WeaponTypeInterface::MACE,
        ];

        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new Ability(
            $unit,
            $disposable,
            $name,
            $icon,
            [
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $unit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_RANDOM_ENEMY,
                    'offense'          => [
                        'damage_type'     => 1,
                        'physical_damage' => 36,
                        'attack_speed'    => 1,
                        'accuracy'        => 252,
                        'magic_accuracy'  => 413,
                        'block_ignoring'  => 0,
                    ],
                    'can_be_avoided'   => true,
                    'name'             => $name,
                    'animation_method' => 'damage',
                    'message_method'   => 'damageAbility',
                    'icon'             => $icon,
                ],
            ],
            $typeActivate = AbilityInterface::ACTIVATE_RAGE,
            $allowedWeaponType,
            $chanceActivate
        );

        self::assertEquals($unit, $ability->getUnit());
        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($disposable, $ability->isDisposable());
        self::assertEquals($typeActivate, $ability->getTypeActivate());
        self::assertEquals($allowedWeaponType, $ability->getAllowedWeaponTypes());

        // Проверка значений по-умолчанию:
        self::assertFalse($ability->isReady());
        self::assertFalse($ability->isUsage());
        self::assertEquals($chanceActivate, $ability->getChanceActivate());
    }

    /**
     * Тест на ситуацию, когда передан некорректный массив данных по способностей
     *
     * @throws Exception
     */
    public function testAbilityInvalidActionsData(): void
    {
        $unit = UnitFactory::createByTemplate(1);

        $this->expectException(AbilityException::class);
        $this->expectExceptionMessage(AbilityException::INVALID_ACTION_DATA);

        new Ability(
            $unit,
            false,
            'Heavy Strike',
            '/images/icons/ability/335.png',
            [
                'invalid_data',
            ],
            AbilityInterface::ACTIVATE_CONCENTRATION,
            [],
            0
        );
    }

    /**
     * Тест на неизвестный тип активации способности
     *
     * @throws Exception
     */
    public function testAbilityUnknownTypeActivate(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $typeActivate = 10;

        $this->expectException(AbilityException::class);
        $this->expectExceptionMessage(AbilityException::UNKNOWN_ACTIVATE_TYPE . ': ' . $typeActivate);

        new Ability(
            $unit,
            false,
            'Heavy Strike',
            '/images/icons/ability/335.png',
            [
                [],
            ],
            $typeActivate,
            [],
            0
        );
    }

    /**
     * Тест на ситуацию, когда передан невалидный массив параметров для создания эффекта - нет on_apply_actions,
     * on_next_round_actions или on_disable_actions
     *
     * @dataProvider invalidEffectDataProvider
     * @param array $effectData
     * @throws Exception
     */
    public function testAbilityInvalidEffectActionData(array $effectData): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new Ability(
            $unit,
            false,
            'Heavy Strike',
            '/images/icons/ability/335.png',
            [
                [
                    'type'           => ActionInterface::EFFECT,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'Battle Fury',
                    'icon'           => '/images/icons/ability/102.png',
                    'message_method' => 'applyEffect',
                    'effect'         => $effectData,
                ],
            ],
            AbilityInterface::ACTIVATE_CONCENTRATION,
            [],
            0
        );

        $this->expectException(AbilityException::class);
        $this->expectExceptionMessage(AbilityException::INVALID_EFFECT_DATA);

        $ability->getActions($enemyCommand, $command);
    }

    /**
     * У этого теста одна цель - покрыть тестами строчку:
     * "$this->addStageParameters($onDisableActionData, $enemyCommand, $alliesCommand);"
     *
     * Заодно проверяется создание Action для завершения эффекта
     *
     * @throws Exception
     */
    public function testAbilitySetStageParameters(): void
    {
        $name = 'Battle Fury';
        $icon = '/images/icons/ability/102.png';

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
                    'type'           => ActionInterface::EFFECT,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => $name,
                    'icon'           => $icon,
                    'message_method' => 'applyEffect',
                    'effect'         => [
                        'name'                  => $name,
                        'icon'                  => $icon,
                        'duration'              => 15,
                        'on_apply_actions'      => [],
                        'on_next_round_actions' => [],
                        'on_disable_actions'    => [
                            [
                                'type'           => ActionInterface::BUFF,
                                'type_target'    => ActionInterface::TARGET_SELF,
                                'name'           => $name,
                                'modify_method'  => $modifyMethod = 'multiplierAttackSpeed',
                                'power'          => $power = 140,
                                'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
                            ],
                        ],
                    ],
                ],
            ],
            AbilityInterface::ACTIVATE_RAGE,
            [],
            0
        );

        foreach ($ability->getActions($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        self::assertCount(1, $unit->getEffects());

        foreach ($unit->getEffects() as $effect) {
            self::assertEquals($name, $effect->getName());
            self::assertEquals($icon, $effect->getIcon());

            foreach ($effect->getOnDisableActions() as $onDisableAction) {
                self::assertEquals($modifyMethod, $onDisableAction->getModifyMethod());
                self::assertEquals($power, $onDisableAction->getPower());
            }
        }
    }

    /**
     * Тест на ситуацию, когда тип оружия юнита не подходит для активации способности, и она не активируется несмотря
     * на 100% заполненную концентрацию
     *
     * @throws Exception
     */
    public function testAbilityNotAllowedWeaponType(): void
    {
        $name = 'Heavy Strike';
        $unit = UnitFactory::createByTemplate(40); // unit with staff weapon
        $ability = $this->createAbilityByDataProvider($unit, $name);
        $collection = new AbilityCollection();
        $collection->add($ability);

        // Up unit concentration
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        $collection->update($unit);

        self::assertFalse($ability->isReady());
    }

    /**
     * @return array
     */
    public function invalidEffectDataProvider(): array
    {
        return [
            [
                // Отсутствует on_apply_actions
                [
                    'name'                  => 'Battle Fury',
                    'icon'                  => '/images/icons/ability/102.png',
                    'duration'              => 15,
                    'on_next_round_actions' => [],
                    'on_disable_actions'    => [],
                ],
            ],
            [
                // Отсутствует on_next_round_actions
                [
                    'name'               => 'Battle Fury',
                    'icon'               => '/images/icons/ability/102.png',
                    'duration'           => 15,
                    'on_apply_actions'   => [],
                    'on_disable_actions' => [],
                ],
            ],
            [
                // Отсутствует on_disable_actions
                [
                    'name'                  => 'Battle Fury',
                    'icon'                  => '/images/icons/ability/102.png',
                    'duration'              => 15,
                    'on_apply_actions'      => [],
                    'on_next_round_actions' => [],
                ],
            ],
        ];
    }

    /**
     * @param UnitInterface $unit
     * @param string $abilityName
     * @param int $abilityLevel
     * @return AbilityInterface
     * @throws Exception
     */
    private function createAbilityByDataProvider(UnitInterface $unit, string $abilityName, int $abilityLevel = 1): AbilityInterface
    {
        $container = new Container();

        return $container->getAbilityFactory()->create(
            $unit,
            $container->getAbilityDataProvider()->get($abilityName, $abilityLevel)
        );
    }
}
