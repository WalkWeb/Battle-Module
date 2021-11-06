<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Orc;

use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\Effect\ReserveForcesAbility;
use Battle\Unit\Effect\EffectFactory;
use Battle\Unit\Effect\EffectInterface;
use Battle\Unit\UnitInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Battle\Command\CommandFactory;
use Tests\Battle\Factory\UnitFactory;

class TitanTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCreateTitanClass(): void
    {
        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $class = $unit->getClass();

        self::assertEquals(5, $class->getId());
        self::assertEquals('Titan', $class->getName());
        self::assertEquals('/images/icons/small/titan.png', $class->getSmallIcon());

        $abilities = $class->getAbilities($unit);

        foreach ($abilities as $ability) {
            self::assertContainsOnlyInstancesOf(ReserveForcesAbility::class, [$ability]);

            $actions = $ability->getAction($enemyCommand, $command);

            foreach ($actions as $action) {
                self::assertEquals(
                    $this->createEffect($unit, $enemyCommand, $command),
                    $action->getEffect()
                );
            }
        }
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return EffectInterface
     * @throws Exception
     */
    private function createEffect(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    ): EffectInterface
    {
        $factory = new EffectFactory(new ActionFactory());

        $data = [
            'name'                  => 'Reserve Forces',
            'icon'                  => '/images/icons/ability/156.png',
            'duration'              => 6,
            'on_apply_actions'      => [
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $unit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $alliesCommand,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'use Reserve Forces',
                    'modify_method'  => 'multiplierMaxLife',
                    'power'          => 130,
                    'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
                ],
            ],
            'on_next_round_actions' => [],
            'on_disable_actions'    => [],
        ];

        return $factory->create($data);
    }
}
