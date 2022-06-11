<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability;

use Battle\Action\ActionInterface;
use Battle\Command\CommandFactory;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Ability\AbilityException;
use Battle\Unit\Ability\AbilityInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

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
                    'damage'           => 50,
                    'can_be_avoided'   => true,
                    'name'             => $name,
                    'animation_method' => 'damageAbility',
                    'message_method'   => 'damageAbility',
                    'icon'             => $icon,
                ],
            ],
            AbilityInterface::ACTIVATE_CONCENTRATION,
            0
        );

        self::assertEquals($unit, $ability->getUnit());
        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($disposable, $ability->isDisposable());

        // Проверка значений по-умолчанию:
        self::assertFalse($ability->isReady());
        self::assertFalse($ability->isUsage());
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
            0
        );
    }
}
