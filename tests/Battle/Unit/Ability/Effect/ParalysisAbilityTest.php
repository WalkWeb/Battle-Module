<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Effect\ParalysisAbility;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class ParalysisAbilityTest extends AbstractUnitTest
{
    /**
     * Тест на создание способности ParalysisAction
     *
     * @throws Exception
     */
    public function testParalysisAbilityCreate(): void
    {
        $name = 'Paralysis';
        $icon = '/images/icons/ability/086.png';

        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new ParalysisAbility($unit);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertFalse($ability->isDisposable());
        self::assertFalse($ability->isUsage());

        // Up rage
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);

        self::assertTrue($ability->isReady());

        self::assertEquals(
            $this->getParalysisActions($unit, $enemyCommand, $command),
            $ability->getAction($enemyCommand, $command)
        );

        $ability->usage();
        self::assertTrue($ability->isUsage());
        self::assertFalse($ability->isReady());
    }

    /**
     * @throws Exception
     */
    public function testParalysisAbilityCanByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new ParalysisAbility($unit);

        // Изначально эффектов на юните нет
        self::assertCount(0, $enemyUnit->getEffects());

        // Перед применением способности эффекта на юните еще нет - способность может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Эффект появляется
        self::assertCount(1, $enemyUnit->getEffects());

        // После появления эффекта на юните - способность уже не может быть применена
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Пропускаем ходы - заполняем ярость. А также сбрасываем эффект у противника
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();

            // Длительность эффектов обновляется в getAfterActions()
            foreach ($enemyUnit->getAfterActions() as $afterAction) {
                if ($afterAction->canByUsed()) {
                    $afterAction->handle();
                }
            }
        }

        // Эффект исчез
        self::assertCount(0, $enemyUnit->getEffects());

        // Способность опять может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws Exception
     */
    private function getParalysisActions(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    ): ActionCollection
    {
        $actionFactory = new ActionFactory();
        $collection = new ActionCollection();

        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $alliesCommand,
            'type_target'    => ActionInterface::TARGET_RANDOM_ENEMY,
            'name'           => 'Paralysis',
            'icon'           => '/images/icons/ability/086.png',
            'message_method' => 'applyEffect',
            'effect'         => [
                'name'                  => 'Paralysis',
                'icon'                  => '/images/icons/ability/086.png',
                'duration'              => 2,
                'on_apply_actions'      => [],
                'on_next_round_actions' => [
                    [
                        'type'             => ActionInterface::PARALYSIS,
                        'action_unit'      => $unit,
                        'enemy_command'    => $enemyCommand,
                        'allies_command'   => $alliesCommand,
                        'type_target'      => ActionInterface::TARGET_SELF,
                        'name'             => 'Paralysis',
                        'can_be_avoided'   => false,
                        'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                        'message_method'   => DamageAction::EFFECT_MESSAGE_METHOD,
                        'icon'             => '/images/icons/ability/086.png',
                    ],
                ],
                'on_disable_actions'    => [],
            ],
        ];

        $collection->add($actionFactory->create($data));

        return $collection;
    }
}
