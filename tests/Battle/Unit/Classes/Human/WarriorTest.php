<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Human;

use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\Damage\HeavyStrikeAbility;
use Battle\Unit\Ability\Effect\BlessedShieldAbility;
use Battle\Unit\Effect\EffectFactory;
use Battle\Unit\Effect\EffectInterface;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class WarriorTest extends AbstractUnitTest
{
    /**
     * @throws CommandException
     * @throws UnitException
     * @throws Exception
     */
    public function testCreateWarriorClass(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $warrior = $unit->getClass();

        self::assertEquals(1, $warrior->getId());
        self::assertEquals('Warrior', $warrior->getName());
        self::assertEquals('/images/icons/small/warrior.png', $warrior->getSmallIcon());

        $abilities = $warrior->getAbilities($unit);

        foreach ($abilities as $i => $ability) {
            if ($i === 0) {
                self::assertContainsOnlyInstancesOf(HeavyStrikeAbility::class, [$ability]);

                $actions = $ability->getAction($enemyCommand, $command);

                foreach ($actions as $action) {
                    self::assertEquals((int)($unit->getDamage() * 2.5), $action->getPower());
                }
            }

            if ($i === 1) {
                self::assertContainsOnlyInstancesOf(BlessedShieldAbility::class, [$ability]);

                $actions = $ability->getAction($enemyCommand, $command);

                foreach ($actions as $action) {
                    self::assertEquals(
                        $this->createBlessedShieldEffect($unit, $enemyCommand, $command),
                        $action->getEffect()
                    );
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function testWarriorReadyAbility(): void
    {
        $unit = UnitFactory::createByTemplate(1);
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
     * @param CommandInterface $alliesCommand
     * @return EffectInterface
     * @throws Exception
     */
    private function createBlessedShieldEffect(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    ): EffectInterface
    {
        $factory = new EffectFactory(new ActionFactory());

        $data = [
            'name'                  => 'Blessed Shield',
            'icon'                  => '/images/icons/ability/271.png',
            'duration'              => 6,
            'on_apply_actions'      => [
                [
                    'type'           => ActionInterface::BUFF,
                    'action_unit'    => $unit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $alliesCommand,
                    'type_target'    => ActionInterface::TARGET_SELF,
                    'name'           => 'Blessed Shield',
                    'modify_method'  => 'addBlock',
                    'power'          => 15,
                    'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
                ],
            ],
            'on_next_round_actions' => [],
            'on_disable_actions'    => [],
        ];

        return $factory->create($data);
    }
}
