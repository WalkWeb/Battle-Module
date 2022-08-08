<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Bosses;

use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\Ability;
use Battle\Unit\Effect\EffectFactory;
use Battle\Unit\Effect\EffectInterface;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class WardenTest extends AbstractUnitTest
{
    /**
     * @throws Exception
     */
    public function testCreateWardenClass(): void
    {
        $unit = UnitFactory::createByTemplate(27);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $warden = $unit->getClass();

        self::assertEquals(50, $warden->getId());
        self::assertEquals('Warden', $warden->getName());
        self::assertEquals('/images/icons/small/base-inferno.png', $warden->getSmallIcon());

        $abilities = $warden->getAbilities($unit);

        foreach ($abilities as $i => $ability) {

            if ($i === 0) {
                self::assertContainsOnlyInstancesOf(Ability::class, [$ability]);

                $actions = $ability->getActions($enemyCommand, $command);

                foreach ($actions as $action) {
                    self::assertEquals(30, $action->getOffense()->getPhysicalDamage());
                }
            }

            if ($i === 1) {
                self::assertContainsOnlyInstancesOf(Ability::class, [$ability]);

                $actions = $ability->getActions($enemyCommand, $command);

                foreach ($actions as $action) {
                    self::assertEquals(
                        $this->createEffect($unit, $enemyCommand, $command),
                        $action->getEffect()
                    );
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function testWardenReadyAbility(): void
    {
        $unit = UnitFactory::createByTemplate(27);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        for ($i = 0; $i < 30; $i++) {
            $unit->newRound();
        }

        foreach ($unit->getAbilities() as $ability) {
            self::assertTrue($ability->isReady());
            self::assertTrue($ability->canByUsed($enemyCommand, $command));
        }
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @return EffectInterface
     * @throws Exception
     */
    private function createEffect(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command
    ): EffectInterface
    {
        $effectFactory = new EffectFactory(new ActionFactory());

        $data = [
            'name'                  => 'Incineration',
            'icon'                  => '/images/icons/ability/232.png',
            'duration'              => 8,
            'on_apply_actions'      => [],
            'on_next_round_actions' => [
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $unit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'name'             => 'Incineration',
                    'offense'          => [
                        'type_damage'         => 2,
                        'physical_damage'     => 6,
                        'attack_speed'        => 1,
                        'accuracy'            => 500,
                        'magic_accuracy'      => 500,
                        'block_ignore'        => 0,
                        'critical_chance'     => 0,
                        'critical_multiplier' => 0,
                    ],
                    'can_be_avoided'   => false,
                    'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                    'message_method'   => DamageAction::EFFECT_MESSAGE_METHOD,
                    'icon'             => '/images/icons/ability/232.png',
                ],
            ],
            'on_disable_actions'    => [],
        ];

        return $effectFactory->create($data);
    }
}
