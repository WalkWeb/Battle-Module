<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Command\CommandInterface;
use Battle\Unit\Effect\Effect;
use Battle\Unit\Effect\EffectFactory;
use Battle\Unit\Effect\EffectInterface;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\BaseFactory;

class EffectTest extends AbstractUnitTest
{
    /**
     * @throws ActionException
     */
    public function testEffectCreate(): void
    {
        $name = 'Effect Name';
        $icon = 'path_to_icon.png';
        $duration = 10;
        $onApplyActions = new ActionCollection();
        $onNextRoundActions = new ActionCollection();
        $onDisableActions = new ActionCollection();

        $effect = new Effect($name, $icon, $duration, $onApplyActions, $onNextRoundActions, $onDisableActions);

        self::assertEquals($name, $effect->getName());
        self::assertEquals($icon, $effect->getIcon());
        self::assertEquals($duration, $effect->getDuration());
        self::assertEquals($duration, $effect->getBaseDuration());
        self::assertEquals($onApplyActions, $effect->getOnApplyActions());
        self::assertEquals($onNextRoundActions, $effect->getOnNextRoundActions());
        self::assertEquals($onDisableActions, $effect->getOnDisableActions());

        $effect->nextRound();
        $effect->nextRound();
        $effect->nextRound();

        self::assertEquals($duration - 3, $effect->getDuration());

        $effect->resetDuration();

        self::assertEquals($duration, $effect->getDuration());
    }

    /**
     * @throws Exception
     */
    public function testEffectChangeActionUnit(): void
    {
        [$unit, $command, $enemyCommand, $enemyUnit] = BaseFactory::create(1, 2);

        $effect = $this->createEffect($unit, $command, $enemyCommand);

        foreach ($effect->getOnApplyActions() as $action) {
            self::assertEquals($unit, $action->getActionUnit());
        }

        foreach ($effect->getOnNextRoundActions() as $action) {
            self::assertEquals($unit, $action->getActionUnit());
        }

        foreach ($effect->getOnDisableActions() as $action) {
            self::assertEquals($unit, $action->getActionUnit());
        }

        $effect->changeActionUnit($enemyUnit);

        foreach ($effect->getOnApplyActions() as $action) {
            self::assertEquals($enemyUnit, $action->getActionUnit());
        }

        foreach ($effect->getOnNextRoundActions() as $action) {
            self::assertEquals($enemyUnit, $action->getActionUnit());
        }

        foreach ($effect->getOnDisableActions() as $action) {
            self::assertEquals($enemyUnit, $action->getActionUnit());
        }
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $command
     * @param CommandInterface $enemyCommand
     * @return EffectInterface
     * @throws Exception
     */
    private function createEffect(UnitInterface $unit, CommandInterface $command, CommandInterface $enemyCommand): EffectInterface
    {


        $factory = new EffectFactory(new ActionFactory());

        $data = [
            'name'                  => 'Effect test #1',
            'icon'                  => 'effect_icon_#1',
            'duration'              => 10,
            'on_apply_actions'      => [
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $unit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                ]
            ],
            'on_next_round_actions' => [
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $unit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                ]
            ],
            'on_disable_actions'    => [
                [
                    'type'           => ActionInterface::DAMAGE,
                    'action_unit'    => $unit,
                    'enemy_command'  => $enemyCommand,
                    'allies_command' => $command,
                    'type_target'    => ActionInterface::TARGET_SELF,
                ]
            ],
        ];

        return $factory->create($data);
    }
}
