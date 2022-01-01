<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Container\Container;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Effect\BattleFuryAbility;
use Battle\Unit\UnitInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\UnitFactory;

class BattleFuryAbilityTest extends TestCase
{
    private const MESSAGE = '<span style="color: #ae882d">Titan</span> use <img src="/images/icons/ability/102.png" alt="" /> Battle Fury';

    /**
     * Тест на создание способности BattleFuryAbility
     *
     * @throws Exception
     */
    public function testBattleFuryAbilityCreate(): void
    {
        $name = 'Battle Fury';
        $icon = '/images/icons/ability/102.png';

        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new BattleFuryAbility($unit);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        // Up concentration
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);

        self::assertTrue($ability->isReady());

        self::assertEquals(
            $this->getBattleFuryActions($unit, $enemyCommand, $command),
            $ability->getAction($enemyCommand, $command)
        );

        $ability->usage();

        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на применение способности BattleFuryAbility
     *
     * @throws Exception
     */
    public function testBattleFuryAbilityApply(): void
    {
        $container = new Container();
        $power = 1.4;
        $unit = UnitFactory::createByTemplate(21, $container);
        $enemyUnit = UnitFactory::createByTemplate(2, $container);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $oldAttackSpeed = $unit->getAttackSpeed();

        // Up concentration
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();
        }

        // Обнуляем концентрацию, чтобы получить способность связанную с яростью
        $unit->useConcentrationAbility();

        $actions = $unit->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            $message = $action->handle();
            self::assertEquals(self::MESSAGE, $message);
        }

        // Проверяем, что скорость атаки юнита выросла
        self::assertEquals($oldAttackSpeed * $power , $unit->getAttackSpeed());

        // Пропускаем ходы
        for ($i = 0; $i < 30; $i++) {
            $unit->newRound();
        }

        // Проверяем, что скорость атаки вернулась к исходному
        self::assertEquals($oldAttackSpeed, $unit->getAttackSpeed());

        $chatMessages = $container->getChat()->getMessages();

        // Проверяем, что сообщение об использовании способности было добавлено в чат один раз
        self::assertCount(1, $chatMessages);

        foreach ($chatMessages as $chatMessage) {
            self::assertEquals(self::MESSAGE, $chatMessage);
        }
    }

    /**
     * Тест на проверку перехода события из способного к применению, в невозможное к применение и обратно
     *
     * @throws Exception
     */
    public function testBattleFuryAbilityCanByUsed(): void
    {
        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new BattleFuryAbility($unit);

        // Перед применением способности эффекта на юните еще нет - способность может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        foreach ($ability->getAction($enemyCommand, $command) as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // После появления эффекта на юните - способность уже не может быть применена
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Пропускаем ходы
        for ($i = 0; $i < 30; $i++) {
            $unit->newRound();
        }

        // Эффект исчез - способность опять может быть применена
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws Exception
     */
    private function getBattleFuryActions(
        UnitInterface $unit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand
    ): ActionCollection
    {
        $actionFactory = new ActionFactory();
        $collection = new ActionCollection();

        $data = [
            'type'           => ActionInterface::EFFECT,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $alliesCommand,
            'type_target'    => ActionInterface::TARGET_SELF,
            'name'           => 'Battle Fury',
            'icon'           => '/images/icons/ability/102.png',
            'message_method' => 'applyEffectImproved',
            'effect'         => [
                'name'                  => 'Battle Fury',
                'icon'                  => '/images/icons/ability/102.png',
                'duration'              => 15,
                'on_apply_actions'      => [
                    [
                        'type'           => ActionInterface::BUFF,
                        'action_unit'    => $unit,
                        'enemy_command'  => $enemyCommand,
                        'allies_command' => $alliesCommand,
                        'type_target'    => ActionInterface::TARGET_SELF,
                        'name'           => 'Battle Fury',
                        'modify_method'  => 'multiplierAttackSpeed',
                        'power'          => 140,
                        'message_method' => ActionInterface::SKIP_MESSAGE_METHOD,
                    ],
                ],
                'on_next_round_actions' => [],
                'on_disable_actions'    => [],
            ],
        ];

        $collection->add($actionFactory->create($data));

        return $collection;
    }
}
