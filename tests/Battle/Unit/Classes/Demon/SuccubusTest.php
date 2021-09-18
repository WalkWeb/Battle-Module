<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\Demon;

use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\Effect\PoisonAbility;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\Effect\EffectFactory;
use Battle\Unit\UnitInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class SuccubusTest extends TestCase
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

        foreach ($abilities as $ability) {
            self::assertContainsOnlyInstancesOf(PoisonAbility::class, [$ability]);

            $actions = $ability->getAction($enemyCommand, $command);

            foreach ($actions as $action) {
                self::assertEquals(
                    $this->createEffects($unit, $enemyCommand, $command),
                    $action->getEffects()
                );
            }
        }
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $command
     * @param CommandInterface $enemyCommand
     * @return EffectCollection
     * @throws Exception
     */
    private function createEffects(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command
    ): EffectCollection
    {
        $effects = new EffectCollection($unit);
        $effectFactory = new EffectFactory(new ActionFactory());

        $data = [
            'name'                  => 'Poison',
            'icon'                  =>'/images/icons/ability/202.png',
            'duration'              => 5,
            'on_apply_actions'      => [],
            'on_next_round_actions' => [
                [
                    'type'            => ActionInterface::DAMAGE,
                    'action_unit'     => $unit,
                    'enemy_command'   => $enemyCommand,
                    'allies_command'  => $command,
                    'type_target'     => ActionInterface::TARGET_SELF,
                    'name'            => null,
                    'power'           => 8,
                    'animation_method' => DamageAction::EFFECT_ANIMATION_METHOD,
                ],
            ],
            'on_disable_actions'    => [],
        ];

        $effects->add($effectFactory->create($data));

        return $effects;
    }
}
