<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Demon;

use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\HealAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\Effect\PoisonAbility;
use Battle\Unit\Ability\Heal\GeneralHealAbility;
use Battle\Unit\Effect\EffectFactory;
use Battle\Unit\Effect\EffectInterface;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class SuccubusTest extends AbstractUnitTest
{
    /**
     * Тест на создание класса Succubus
     *
     * @throws Exception
     */
    public function testSuccubusCreate(): void
    {
        $unit = UnitFactory::createByTemplate(23);
        $enemyUnit = UnitFactory::createByTemplate(1);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $succubus = $unit->getClass();

        self::assertEquals(7, $succubus->getId());
        self::assertEquals('Succubus', $succubus->getName());
        self::assertEquals('/images/icons/small/dark-mage.png', $succubus->getSmallIcon());

        $abilities = $succubus->getAbilities($unit);

        foreach ($abilities as $i => $ability) {

            if ($i === 0) {
                self::assertContainsOnlyInstancesOf(PoisonAbility::class, [$ability]);

                $actions = $ability->getAction($enemyCommand, $command);

                foreach ($actions as $action) {
                    self::assertEquals(
                        $this->createEffect($unit, $enemyCommand, $command),
                        $action->getEffect()
                    );
                }
            }

            if ($i === 1) {
                self::assertContainsOnlyInstancesOf(GeneralHealAbility::class, [$ability]);

                $actions = $ability->getAction($enemyCommand, $command);

                foreach ($actions as $action) {
                    self::assertEquals(
                        $this->createHeal($unit, $enemyCommand, $command),
                        $action
                    );
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function testSuccubusReadyAbility(): void
    {
        $unit = UnitFactory::createByTemplate(23);
        $woundedUnit = UnitFactory::createByTemplate(11);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit, $woundedUnit]);
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
            'name'                  => 'Poison',
            'icon'                  => '/images/icons/ability/202.png',
            'duration'              => 5,
            'on_apply_actions'      => [],
            'on_next_round_actions' => [
                [
                    'type'             => ActionInterface::DAMAGE,
                    'action_unit'      => $unit,
                    'enemy_command'    => $enemyCommand,
                    'allies_command'   => $command,
                    'type_target'      => ActionInterface::TARGET_SELF,
                    'name'             => 'Poison',
                    'damage'           => 8,
                    'can_be_avoided'   => false,
                    'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                    'message_method'   => DamageAction::EFFECT_MESSAGE_METHOD,
                    'icon'             => '/images/icons/ability/202.png',
                ],
            ],
            'on_disable_actions'    => [],
        ];

        return $effectFactory->create($data);
    }

    private function createHeal(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command
    ): ActionInterface
    {
        return new HealAction(
            $unit,
            $enemyCommand,
            $command,
            HealAction::TARGET_ALL_WOUNDED_ALLIES,
            (int)($unit->getDamage() * 1.2),
            'General Heal',
            HealAction::UNIT_ANIMATION_METHOD,
            HealAction::ABILITY_MESSAGE_METHOD,
            '/images/icons/ability/452.png'
        );
    }
}
