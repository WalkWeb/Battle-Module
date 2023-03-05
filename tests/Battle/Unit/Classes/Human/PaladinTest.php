<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Human;

use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\EffectAction;
use Battle\Action\ParalysisAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Container\Container;
use Battle\Container\ContainerInterface;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Effect\EffectInterface;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Factory\UnitFactory;

class PaladinTest extends AbstractUnitTest
{
    /**
     * Тест на создание класса Paladin
     *
     * @throws Exception
     */
    public function testPaladinCreateClass(): void
    {
        $container = new Container();
        $unit = UnitFactory::createByTemplate(44, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $paladin = $unit->getClass();

        self::assertEquals(8, $paladin->getId());
        self::assertEquals('Paladin', $paladin->getName());
        self::assertEquals('/images/icons/small/paladin.png', $paladin->getSmallIcon());

        $abilities = $paladin->getAbilities($unit);

        self::assertCount(1, $abilities);

        foreach ($abilities as $i => $ability) {

            self::assertContainsOnlyInstancesOf(Ability::class, [$ability]);

            $actions = $ability->getActions($enemyCommand, $command);

            foreach ($actions as $action) {
                self::assertContainsOnlyInstancesOf(EffectAction::class, [$action]);

                self::assertEquals(
                    $this->createStunEffect($container, $unit, $enemyCommand, $command),
                    $action->getEffect()
                );
            }
        }
    }

    /**
     * @param ContainerInterface $container
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return EffectInterface
     * @throws Exception
     */
    private function createStunEffect(
        ContainerInterface $container,
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    ): EffectInterface
    {
        $data = [
            'name'                  => 'Stun',
            'icon'                  => '/images/icons/ability/186.png',
            'duration'              => 2,
            'on_apply_actions'      => [],
            'on_next_round_actions' => [
                [
                    'type'             => ActionInterface::PARALYSIS,
                    'action_unit'      => $unit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $alliesCommand,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'name'             => 'Stun',
                    'can_be_avoided'   => false,
                    'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                    'message_method'   => ParalysisAction::STUN_MESSAGE_METHOD,
                    'icon'             => '/images/icons/ability/186.png',
                ],
            ],
            'on_disable_actions'    => [],
        ];

        return $container->getEffectFactory()->create($data);
    }
}
