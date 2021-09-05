<?php

declare(strict_types=1);

namespace Tests\Battle\Stroke;

use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Action\HealAction;
use Battle\Command\CommandInterface;
use Battle\Stroke\StrokeException;
use Battle\Unit\Race\RaceFactory;
use Battle\Unit\UnitInterface;
use Exception;
use Battle\Container\Container;
use Battle\Command\CommandFactory;
use PHPUnit\Framework\TestCase;
use Battle\Stroke\Stroke;
use Tests\Battle\Factory\Mock\BrokenPriestUnit;
use Tests\Battle\Factory\UnitFactory;

class StrokeTest extends TestCase
{
    private const MESSAGE = '<span style="color: #1e72e3">unit_1</span> attack <span style="color: #1e72e3">unit_2</span> on 20 damage';

    /**
     * Тест на базовую обработку одного хода
     *
     * @throws Exception
     */
    public function testStrokeHandle(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $container = new Container();

        $stroke = new Stroke(1, $unit, $command, $enemyCommand, $container);
        $stroke->handle();

        self::assertEquals($enemyUnit->getTotalLife() - $unit->getDamage(), $enemyUnit->getLife());

        self::assertTrue($unit->isAction());
        self::assertFalse($enemyUnit->isAction());

        $chatResultMessages = [
            self::MESSAGE,
        ];

        self::assertEquals($chatResultMessages, $container->getChat()->getMessages());
    }

    /**
     * Тест на остановку внутри Stroke, например, когда юнит хочет сделать два удара, но противник умирает после первого
     *
     * @throws Exception
     */
    public function testStrokeBreakAction(): void
    {
        $leftUnit = UnitFactory::createByTemplate(13);
        $rightUnit = UnitFactory::createByTemplate(1);

        $leftCommand = CommandFactory::create([$leftUnit]);
        $rightCommand = CommandFactory::create([$rightUnit]);

        $stroke = new Stroke(1, $leftUnit, $leftCommand, $rightCommand, new Container());

        // Для теста достаточно того, что выполнение хода завершилось без ошибок
        $stroke->handle();

        // Но, на всякий случай проверяем, что противник умер
        self::assertEquals(0, $rightUnit->getLife());
    }

    /**
     * Сложный тест на эмуляцию ситуации, когда Stroke не может выполнить полученный Action
     *
     * Сложный тем, что юнит проверяет событие на возможность использование. Значит, делаем мок юнита, который вернет
     * именно лечение, хотя и лечить некого
     *
     * @throws Exception
     */
    public function testStrokeCantBeUsedActionException(): void
    {
        $enemyUnit = UnitFactory::createByTemplate(3);

        $brokenPriest = new BrokenPriestUnit(
            'id',
            'Broken Priest',
            1,
            'avatar',
            20,
            1,
            100,
            100,
            false,
            1,
            RaceFactory::create(1),
            $enemyUnit->getContainer()
        );

        $alliesCommand = CommandFactory::create([$brokenPriest]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $stroke = new Stroke(1, $brokenPriest, $alliesCommand, $enemyCommand, new Container());

        $this->expectException(StrokeException::class);
        $this->expectExceptionMessage(StrokeException::CANT_BE_USED_ACTION);
        $stroke->handle();
    }

    /**
     * Тест на применение эффект при обработке хода
     *
     * @throws Exception
     */
    public function testStrokeApplyEffect(): void
    {
        $unit = UnitFactory::createByTemplate(11);
        $command = CommandFactory::create([$unit]);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $enemyCommand = CommandFactory::create([$enemyUnit]);
        $container = new Container();

        $effectAction = $this->getEffectAction($unit, $command, $enemyCommand);

        self::assertTrue($effectAction->canByUsed());

        $effectAction->handle();

        self::assertCount(1, $unit->getEffects());
        self::assertEquals(1, $unit->getLife());

        $stroke = new Stroke(1, $unit, $command, $enemyCommand, $container);
        $stroke->handle();

        self::assertEquals(16, $unit->getLife());
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $command
     * @param CommandInterface $enemyCommand
     * @return ActionInterface
     * @throws Exception
     */
    private function getEffectAction(UnitInterface $unit, CommandInterface $command, CommandInterface $enemyCommand): ActionInterface
    {
        $actionFactory = new ActionFactory();

        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_SELF,
            'name'           => 'Effect Heal',
            'effects'        => [
                [
                    'name'                  => 'Effect Heal',
                    'icon'                  => 'icon.png',
                    'duration'              => 8,
                    'on_apply_actions'      => [],
                    'on_next_round_actions' => [
                        [
                            'type'             => ActionInterface::HEAL,
                            'action_unit'      => $unit,
                            'enemy_command'    => $enemyCommand,
                            'allies_command'   => $command,
                            'type_target'      => ActionInterface::TARGET_SELF,
                            'name'             => 'Effect Heal',
                            'power'            => 15,
                            'animation_method' => HealAction::EFFECT_ANIMATION_METHOD,
                        ],
                    ],
                    'on_disable_actions'    => [],
                ],
            ],
        ];

        return $actionFactory->create($data);
    }
}
