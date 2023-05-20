<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Dwarf;

use Battle\Action\ActionInterface;
use Battle\Action\HealAction;
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

class AlchemistTest extends AbstractUnitTest
{
    /**
     * @throws Exception
     */
    public function testAlchemistCreate(): void
    {
        $container = new Container();
        $unit = UnitFactory::createByTemplate(22, $container);
        $enemyUnit = UnitFactory::createByTemplate(1, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $alchemist = $unit->getClass();

        self::assertEquals(6, $alchemist->getId());
        self::assertEquals('Alchemist', $alchemist->getName());
        self::assertEquals('/images/icons/small/alchemist.png', $alchemist->getSmallIcon());

        $abilities = $alchemist->getAbilities($unit);

        foreach ($abilities as $i => $ability) {

            if ($i === 0) {
                self::assertContainsOnlyInstancesOf(Ability::class, [$ability]);

                $actions = $ability->getActions($enemyCommand, $command);

                foreach ($actions as $action) {
                    self::assertEquals(
                        $this->createEffect($container, $unit, $enemyCommand, $command),
                        $action->getEffect()
                    );
                }
            }
            if ($i === 1) {
                self::assertContainsOnlyInstancesOf(Ability::class, [$ability]);

                $actions = $ability->getActions($enemyCommand, $command);

                foreach ($actions as $action) {
                    self::assertEquals($this->getSummon()->getName(), $action->getSummonUnit()->getName());
                    self::assertEquals($this->getSummon()->getLevel(), $action->getSummonUnit()->getLevel());
                    self::assertEquals($this->getSummon()->getAvatar(), $action->getSummonUnit()->getAvatar());

                    self::assertEquals(
                        $this->getSummon()->getOffense()->getDamage($enemyUnit->getDefense()),
                        $action->getSummonUnit()->getOffense()->getDamage($enemyUnit->getDefense())
                    );

                    self::assertEquals($this->getSummon()->getOffense()->getAttackSpeed(), $action->getSummonUnit()->getOffense()->getAttackSpeed());
                    self::assertEquals($this->getSummon()->getOffense()->getCastSpeed(), $action->getSummonUnit()->getOffense()->getCastSpeed());
                    self::assertEquals($this->getSummon()->getLife(), $action->getSummonUnit()->getLife());
                    self::assertEquals($this->getSummon()->getTotalLife(), $action->getSummonUnit()->getTotalLife());
                    self::assertEquals($this->getSummon()->isMelee(), $action->getSummonUnit()->isMelee());
                    self::assertEquals($this->getSummon()->getClass(), $action->getSummonUnit()->getClass());
                    self::assertEquals($this->getSummon()->getRace(), $action->getSummonUnit()->getRace());
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function testAlchemistReadyAbility(): void
    {
        $unit = UnitFactory::createByTemplate(22);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        for ($i = 0; $i < 30; $i++) {
            $unit->newRound();
        }

        foreach ($unit->getAbilities() as $i => $ability) {

            // Эффект на лечение - лечить некого
            if ($i === 0) {
                self::assertTrue($ability->isReady());
                self::assertFalse($ability->canByUsed($enemyCommand, $command));
            }

            // Призыв
            if ($i === 1) {
                self::assertTrue($ability->isReady());
                self::assertTrue($ability->canByUsed($enemyCommand, $command));
            }
        }
    }

    /**
     * @param ContainerInterface $container
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @return EffectInterface
     * @throws Exception
     */
    private function createEffect(
        ContainerInterface $container,
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command
    ): EffectInterface
    {
        $data = [
            'name'                  => 'Healing Potion',
            'icon'                  => '/images/icons/ability/234.png',
            'duration'              => 4,
            'on_apply_actions'      => [],
            'on_next_round_actions' => [
                [
                    'type'             => ActionInterface::HEAL,
                    'action_unit'      => $unit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'name'             => 'Healing Potion',
                    'power'            => 15,
                    'animation_method' => HealAction::EFFECT_ANIMATION_METHOD,
                    'message_method'   => HealAction::EFFECT_MESSAGE_METHOD,
                    'icon'             => '/images/icons/ability/234.png',
                    'target_tracking'  => false,
                ],
            ],
            'on_disable_actions'    => [],
        ];

        return $container->getEffectFactory()->create($data);
    }

    /**
     * @return UnitInterface
     * @throws Exception
     */
    private function getSummon(): UnitInterface
    {
        return UnitFactory::createByTemplate(26);
    }
}
