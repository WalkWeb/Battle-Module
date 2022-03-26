<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Effect;

use Battle\Action\ActionCollection;
use Battle\Action\ActionFactory;
use Battle\Action\ActionInterface;
use Battle\Command\CommandFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\Effect\BlessedShieldAbility;
use Battle\Unit\UnitInterface;
use Exception;
use Tests\AbstractUnitTest;
use Tests\Battle\Factory\UnitFactory;

class BlessedShieldAbilityTest extends AbstractUnitTest
{
    private const MESSAGE_EN = '<span style="color: #1e72e3">unit_1</span> use <img src="/images/icons/ability/271.png" alt="" /> <span class="ability">Blessed Shield</span>';
    private const MESSAGE_RU = '<span style="color: #1e72e3">unit_1</span> использовал <img src="/images/icons/ability/271.png" alt="" /> <span class="ability">Благословенный щит</span>';

    /**
     * Тест на создание и использовании способности BlessedShieldAbility
     *
     * @throws Exception
     */
    public function testBlessedShieldAbilityUse(): void
    {
        $name = 'Blessed Shield';
        $icon = '/images/icons/ability/271.png';
        $unit = UnitFactory::createByTemplate(21);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new BlessedShieldAbility($unit);

        self::assertEquals($name, $ability->getName());
        self::assertEquals($icon, $ability->getIcon());
        self::assertEquals($unit, $ability->getUnit());
        self::assertFalse($ability->isReady());
        self::assertTrue($ability->canByUsed($enemyCommand, $command));

        // Up rage
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
            $this->getBlessedShieldActions($unit, $enemyCommand, $command),
            $ability->getAction($enemyCommand, $command)
        );

        $ability->usage();

        self::assertFalse($ability->isReady());
    }

    /**
     * Тест на применение способности BlessedShieldAbility
     *
     * @throws Exception
     */
    public function testBlessedShieldAbilityApply(): void
    {
        $unit = UnitFactory::createByTemplate(1);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new BlessedShieldAbility($unit);

        // Up rage
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
        self::assertTrue($ability->canByUsed($enemyCommand, $command));
        self::assertCount(0, $unit->getEffects());

        // Проверка блока перед использованием
        self::assertEquals(0, $unit->getBlock());

        // Применяем способность
        $actions = $ability->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
            self::assertEquals(self::MESSAGE_EN, $this->getChat()->addMessage($action));
            self::assertEquals(self::MESSAGE_RU, $this->getChatRu()->addMessage($action));
        }

        // Проверяем, что способность больше не может быть использована, т.к. аналогичный эффект уже есть
        self::assertFalse($ability->canByUsed($enemyCommand, $command));

        // Проверка блока после использования
        self::assertEquals(15, $unit->getBlock());
        self::assertCount(1, $unit->getEffects());

        // Пропускаем 10 ходов
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        // И проверяем, что блок вернулся к исходному
        self::assertCount(0, $unit->getEffects());
        self::assertEquals(0, $unit->getBlock());
    }

    /**
     * Тест на ситуацию, когда после прибавления блока блок выше 100, но из-за ограничения в любом случае будет только
     * 100
     *
     * @throws Exception
     */
    public function testBlessedShieldAbilityOverValue(): void
    {
        // Юнит со 100% блоком
        $unit = UnitFactory::createByTemplate(28);
        $enemyUnit = UnitFactory::createByTemplate(2);
        $command = CommandFactory::create([$unit]);
        $enemyCommand = CommandFactory::create([$enemyUnit]);

        $ability = new BlessedShieldAbility($unit);

        // Up rage
        for ($i = 0; $i < 20; $i++) {
            $unit->newRound();
        }

        $collection = new AbilityCollection();
        $collection->add($ability);

        foreach ($collection as $item) {
            self::assertEquals($ability, $item);
        }

        $collection->update($unit);

        // Проверка блока перед использованием
        self::assertEquals(100, $unit->getBlock());

        // Применяем способность
        $actions = $ability->getAction($enemyCommand, $command);

        foreach ($actions as $action) {
            self::assertTrue($action->canByUsed());
            $action->handle();
        }

        // Проверка блока после использования, он также равен 100
        self::assertEquals(100, $unit->getBlock());


        // Пропускаем 10 ходов
        for ($i = 0; $i < 10; $i++) {
            $unit->newRound();
        }

        // И проверяем, что блок вернулся к исходному (не изменился)
        self::assertCount(0, $unit->getEffects());
        self::assertEquals(100, $unit->getBlock());
    }

    /**
     * @param UnitInterface $unit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws Exception
     */
    private function getBlessedShieldActions(
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
            'name'           => 'Blessed Shield',
            'icon'           => '/images/icons/ability/271.png',
            'message_method' => 'applyEffect',
            'effect'         => [
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
            ],
        ];

        $collection->add($actionFactory->create($data));

        return $collection;
    }
}
