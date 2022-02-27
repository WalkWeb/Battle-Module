<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Action\HealAction;
use Battle\Command\CommandException;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\Effect\Effect;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;
use Tests\Battle\Factory\UnitFactoryException;

class EffectCollectionTest extends AbstractUnitTest
{
    /**
     * @throws UnitFactoryException
     */
    public function testEffectCollectionCreate(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $collection = new EffectCollection($unit);

        $collection->add(new Effect(
            'Effect#1',
            'icon',
            10,
            [],
            [],
            []
        ));

        $collection->add(new Effect(
            'Effect#2',
            'icon',
            10,
            [],
            [],
            []
        ));

        self::assertCount(2, $collection);

        $namesExpected = ['Effect#1', 'Effect#2'];

        $i = 0;
        foreach ($collection as $key => $effect) {
            self::assertEquals($namesExpected[$i], $effect->getName());
            $i++;
        }
    }

    /**
     * @throws UnitFactoryException
     */
    public function testEffectCollectionExist(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $collection = new EffectCollection($unit);

        $effect1 = new Effect(
            'Effect#1',
            'icon',
            10,
            [],
            [],
            []
        );

        $effect2 = new Effect(
            'Effect#2',
            'icon',
            10,
            [],
            [],
            []
        );

        $collection->add($effect1);

        self::assertTrue($collection->exist($effect1));
        self::assertFalse($collection->exist($effect2));
    }

    /**
     * Тест на ситуацию, когда добавленный аналогичный эффект обновляет длительность уже существующего
     *
     * @throws ActionException
     * @throws UnitFactoryException
     */
    public function testEffectCollectionAddDouble(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $collection = new EffectCollection($unit);

        $duration = 10;

        $effect = new Effect(
            'Effect#1',
            'icon',
            $duration,
            [],
            [],
            []
        );

        $collection->add($effect);

        $collection->nextRound();
        $collection->nextRound();
        $collection->nextRound();

        foreach ($collection as $effect) {
            self::assertEquals($duration - 3, $effect->getDuration());
        }

        $collection->add($effect);

        self::assertCount(1, $collection);

        foreach ($collection as $effect) {
            self::assertEquals($duration, $effect->getDuration());
        }
    }

    /**
     * Тест на получение лечения у коллекции эффектов при новом раунде
     *
     * @throws CommandException
     * @throws UnitException
     * @throws ActionException
     * @throws UnitFactoryException
     */
    public function testEffectCollectionActionsOnNextRound(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $collection = new EffectCollection($unit);

        $actions = new ActionCollection();
        $actions->add(new HealAction($unit, $enemyCommand, $command, HealAction::TARGET_WOUNDED_ALLIES));

        $collection->add(new Effect(
            'Effect#1',
            'icon',
            5,
            [],
            $actions,
            []
        ));

        // 5 раз получаем ActionCollection с HealAction внутри
        for ($i = 0; $i < 5; $i++) {
            self::assertEquals($actions, $collection->newRound());
            $collection->nextRound();
        }

        // Затем коллекция эффектов становится пустой - эффект удалился
        self::assertCount(0, $collection);
    }

    /**
     * @throws ActionException
     * @throws CommandException
     * @throws UnitException
     * @throws UnitFactoryException
     */
    public function testEffectCollectionActionsOnDisableActions(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $collection = new EffectCollection($unit);

        $actions = new ActionCollection();
        $actions->add(new HealAction($unit, $enemyCommand, $command, HealAction::TARGET_WOUNDED_ALLIES));

        $collection->add(new Effect(
            'Effect#1',
            'icon',
            5,
            [],
            [],
            $actions
        ));

        // 4 раз получаем пустую ActionCollection
        for ($i = 0; $i < 4; $i++) {
            self::assertEquals(new ActionCollection(), $collection->nextRound());
        }

        // А на 5 раз получаем ActionCollection с HealAction
        self::assertEquals($actions, $collection->nextRound());

        // Затем коллекция эффектов становится пустой - эффект удалился
        self::assertCount(0, $collection);
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $command
     * @param int $typeTarget
     * @return array[]
     */
    private function getHealActionData(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $command,
        int $typeTarget
    ): array
    {
        return [
            [
                'type'           => ActionInterface::EFFECT,
                'action_unit'    => $unit,
                'enemy_command'  => $enemyCommand,
                'allies_command' => $command,
                'type_target'    => $typeTarget,
                'name'           => 'use Reserve Forces',
                'effect'         => [
                    'name'                  => 'Effect#123',
                    'icon'                  => 'icon.png',
                    'duration'              => 8,
                    'on_apply_actions'      => [
                        [
                            'type'           => ActionInterface::BUFF,
                            'action_unit'    => $unit,
                            'enemy_command'  => $enemyCommand,
                            'allies_command' => $command,
                            'type_target'    => ActionInterface::TARGET_SELF,
                            'name'           => 'use Reserve Forces',
                            'modify_method'  => 'multiplierMaxLife',
                            'power'          => 130,
                        ],
                    ],
                    'on_next_round_actions' => [],
                    'on_disable_actions'    => [],
                ],
            ]
        ];
    }
}
